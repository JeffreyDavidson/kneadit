<?php

namespace App\Actions\Orders;

use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Orders\OrderRefundInProgressException;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Loyalty\LoyaltyLedger;
use App\Services\Stripe\StripeSettingsReader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

/**
 * Issues a Stripe refund for an order's captured payment, records a Refund
 * row, and flips the order's payment_status to Refunded.
 *
 * Refunds the full order total — partial refunds are out of scope for this
 * action (would need a $amount parameter and a separate status for partial
 * refunds, which is tracked as follow-up Option B).
 *
 * A full refund also takes back the loyalty points the order earned.
 *
 * No network call runs inside a database transaction (SQLite would hold a read
 * snapshot across it and then fail to write), so the work is three steps:
 *
 * 1. Claim the refund with one atomic conditional UPDATE that sets
 *    `refund_claimed_at` only while the order is Paid and not already claimed.
 *    Only the request that updates a row goes on; a request holding a stale
 *    copy updates nothing and never reaches Stripe.
 * 2. Call Stripe, with an idempotency key stable per order and payment intent.
 *    If Stripe refuses, the claim is cleared so a later retry can proceed.
 * 3. Record the Refund row and the order's new payment status in a short
 *    transaction.
 *
 * A claim older than CLAIM_TTL_MINUTES counts as abandoned (the process died
 * between steps 2 and 3) and can be claimed again; the idempotency key makes
 * Stripe hand back the original refund rather than create a second.
 *
 * No-op (returns null) when:
 * - The order has no stripe_payment_intent_id (paid via PayPal, manual, etc.).
 * - The order's payment_status isn't Paid (nothing to refund, or already refunded).
 *
 * @throws OrderRefundInProgressException when another request holds a live claim on the order
 * @throws StripeRefundFailedException when Stripe refuses the refund
 */
class RefundStripePayment
{
    private const int CLAIM_TTL_MINUTES = 10;

    public function __construct(
        private readonly StripeClient $stripe,
        private readonly StripeSettingsReader $settings,
        private readonly LoyaltyLedger $loyaltyLedger,
    ) {}

    public function __invoke(Order $order, ?User $initiatedBy = null, ?string $reason = null): ?Refund
    {
        if ($order->payment_status !== PaymentStatus::Paid) {
            return null;
        }

        if (! $order->stripe_payment_intent_id) {
            return null;
        }

        if (! $this->claim($order)) {
            $order->refresh();

            // Still Paid means another request is mid-refund; anything else
            // means an earlier request already settled it.
            throw_if($order->payment_status === PaymentStatus::Paid, OrderRefundInProgressException::class, $order);

            return null;
        }

        // A stale copy's intent and total may be out of date, so read them now that the claim is held.
        $order->refresh();
        $paymentIntentId = $order->stripe_payment_intent_id;

        if (! $paymentIntentId) {
            $this->releaseClaim($order);

            return null;
        }

        try {
            $stripeRefund = $this->stripe->refunds->create(
                [
                    'payment_intent' => $paymentIntentId,
                    'reason' => 'requested_by_customer',
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'order_number' => (string) $order->order_number,
                    ],
                ],
                $this->options($order, $paymentIntentId),
            );
        } catch (ApiErrorException $e) {
            $this->releaseClaim($order);

            throw new StripeRefundFailedException(
                order: $order,
                paymentIntentId: $paymentIntentId,
                stripeErrorCode: $e->getStripeCode(),
                previous: $e,
            );
        } catch (Throwable $e) {
            $this->releaseClaim($order);

            throw $e;
        }

        return DB::transaction(function () use ($order, $initiatedBy, $reason, $stripeRefund): Refund {
            $refund = Refund::query()->create([
                'order_id' => $order->id,
                'user_id' => $initiatedBy?->id,
                'amount' => $order->total,
                'reason' => $reason,
                'stripe_refund_id' => $stripeRefund->id,
            ]);

            $order->forceFill([
                'payment_status' => PaymentStatus::Refunded,
                'refund_claimed_at' => null,
            ])->save();

            $this->loyaltyLedger->reverseOrder($order);

            Log::info('Stripe refund issued', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount_cents' => $order->total->cents(),
                'stripe_refund_id' => $stripeRefund->id,
                'initiated_by_user_id' => $initiatedBy?->id,
            ]);

            return $refund;
        });
    }

    /** Whether this call won the claim: one UPDATE, so only one concurrent request can. */
    private function claim(Order $order): bool
    {
        return Order::query()
            ->whereKey($order->id)
            ->where('payment_status', PaymentStatus::Paid)
            ->where(fn (Builder $query) => $query
                ->whereNull('refund_claimed_at')
                ->orWhere('refund_claimed_at', '<', now()->subMinutes(self::CLAIM_TTL_MINUTES)))
            ->update(['refund_claimed_at' => now()]) === 1;
    }

    private function releaseClaim(Order $order): void
    {
        Order::query()->whereKey($order->id)->update(['refund_claimed_at' => null]);
    }

    /**
     * The idempotency key is stable per order and payment intent, so a retried
     * or concurrent call gets Stripe's original refund back instead of a second.
     *
     * @return array{idempotency_key: string, stripe_account?: string}
     */
    private function options(Order $order, string $paymentIntentId): array
    {
        $idempotencyKey = "refund-{$order->id}-{$paymentIntentId}";
        $connectId = $this->settings->connectId();

        if (! $connectId) {
            return ['idempotency_key' => $idempotencyKey];
        }

        return ['idempotency_key' => $idempotencyKey, 'stripe_account' => $connectId];
    }
}
