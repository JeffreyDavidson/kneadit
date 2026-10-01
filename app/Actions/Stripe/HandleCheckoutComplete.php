<?php

namespace App\Actions\Stripe;

use App\Actions\Orders\MarkOrderPaid;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Log;

class HandleCheckoutComplete
{
    public function __construct(
        private readonly MarkOrderPaid $markOrderPaid,
        private readonly ReportStripeAmountMismatch $reportAmountMismatch,
    ) {}

    /**
     * Mark the order paid when the amount Stripe collected matches its total.
     *
     * A different amount leaves the order unpaid and flags it for the baker to review.
     */
    public function __invoke(Order $order, ?string $paymentIntentId, int $amountPaidCents): Order
    {
        $amountMatches = $amountPaidCents === $order->total->cents();

        if (! $amountMatches && $order->payment_status !== PaymentStatus::Paid) {
            ($this->reportAmountMismatch)($order, $paymentIntentId, $amountPaidCents);

            return $order;
        }

        $order->update([
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);

        ($this->markOrderPaid)($order);

        Log::info('Order marked as paid via Stripe', ['order' => $order->order_number]);

        return $order;
    }
}
