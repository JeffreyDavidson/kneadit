<?php

namespace App\Actions\Orders;

use App\Enums\Orders\PaymentStatus;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Issues a Stripe refund for an order's captured payment, records a Refund
 * row, and flips the order's payment_status to Refunded.
 *
 * Refunds the full order total — partial refunds are out of scope for this
 * action (would need a $amount parameter and a separate status for partial
 * refunds, which is tracked as follow-up Option B).
 *
 * Idempotency: refuses to re-refund an order that's already Refunded or
 * Cancelled (payment-wise).
 *
 * No-op (returns null) when:
 * - The order has no stripe_payment_intent_id (paid via PayPal, manual, etc.).
 * - The order's payment_status isn't Paid (nothing to refund).
 */
class RefundStripePayment
{
    public function __construct(
        private StripeClient $stripe,
    ) {}

    public function __invoke(Order $order, ?User $initiatedBy = null, ?string $reason = null): ?Refund
    {
        if ($order->payment_status !== PaymentStatus::Paid) {
            return null;
        }

        if (! $order->stripe_payment_intent_id) {
            return null;
        }

        return DB::transaction(function () use ($order, $initiatedBy, $reason): Refund {
            try {
                $stripeRefund = $this->stripe->refunds->create([
                    'payment_intent' => $order->stripe_payment_intent_id,
                    'reason' => 'requested_by_customer',
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'order_number' => (string) $order->order_number,
                    ],
                ]);
            } catch (ApiErrorException $e) {
                Log::error('Stripe refund failed', [
                    'order_id' => $order->id,
                    'payment_intent' => $order->stripe_payment_intent_id,
                    'error' => $e->getMessage(),
                ]);
                throw new RuntimeException('Stripe refund failed: ' . $e->getMessage(), 0, $e);
            }

            $refund = Refund::query()->create([
                'order_id' => $order->id,
                'user_id' => $initiatedBy?->id,
                'amount' => $order->total,
                'reason' => $reason,
                'stripe_refund_id' => $stripeRefund->id,
            ]);

            $order->forceFill(['payment_status' => PaymentStatus::Refunded])->save();

            return $refund;
        });
    }
}
