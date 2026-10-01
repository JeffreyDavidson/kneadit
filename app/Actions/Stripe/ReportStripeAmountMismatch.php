<?php

declare(strict_types=1);

namespace App\Actions\Stripe;

use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\ValueObjects\Money;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Handles a completed Stripe payment whose amount differs from the order's current total.
 *
 * The order is left unpaid for the baker to review. The payment intent is stored so the
 * payment can be found and refunded or reconciled from Stripe.
 */
class ReportStripeAmountMismatch
{
    public function __invoke(Order $order, ?string $paymentIntentId, int $amountPaidCents): void
    {
        $alreadyReported = $paymentIntentId !== null
            && $order->stripe_payment_intent_id === $paymentIntentId;

        if ($alreadyReported) {
            return;
        }

        Log::warning('Stripe payment amount does not match the order total', [
            'order' => $order->order_number,
            'payment_intent' => $paymentIntentId,
            'paid_cents' => $amountPaidCents,
            'order_total_cents' => $order->total->cents(),
        ]);

        $order->update(['stripe_payment_intent_id' => $paymentIntentId]);

        $paid = Money::fromCents($amountPaidCents)->formatted();
        $total = $order->total->formatted();

        Notification::make()
            ->title("Stripe payment needs review for order {$order->order_number}")
            ->body("The customer paid {$paid} by card, but the order total is {$total}. The order has been left unpaid until you review the payment in Stripe.")
            ->warning()
            ->sendToDatabase(User::query()->owners()->get());
    }
}
