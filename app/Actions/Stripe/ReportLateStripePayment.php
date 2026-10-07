<?php

declare(strict_types=1);

namespace App\Actions\Stripe;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\ValueObjects\Money;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Handles a completed Stripe payment for an order that can no longer take it: the
 * order was cancelled, or it was already paid another way.
 *
 * The order is left exactly as it is, and its recorded payment intent is not
 * overwritten, because a refund of the real payment depends on it. The baker is
 * told once per payment, with the payment intent so it can be found and refunded
 * in Stripe. Both completion paths (the success redirect and the Connect webhook)
 * can report the same payment, so the first report claims it.
 */
class ReportLateStripePayment
{
    public function __invoke(Order $order, ?string $paymentIntentId, int $amountPaidCents): void
    {
        $paymentKey = $paymentIntentId ?? "order-{$order->id}";
        $firstReport = Cache::add("order-late-stripe-payment:{$paymentKey}", true, now()->addDays(7));

        if (! $firstReport) {
            return;
        }

        Log::warning('Stripe payment arrived for an order that can no longer take it', [
            'order' => $order->order_number,
            'payment_intent' => $paymentIntentId,
            'paid_cents' => $amountPaidCents,
            'order_status' => $order->status->value,
            'order_payment_status' => $order->payment_status->value,
        ]);

        $paid = Money::fromCents($amountPaidCents)->formatted();
        $reason = $order->status === OrderStatus::Cancelled
            ? 'the order was cancelled'
            : 'the order was already marked paid';
        $reference = $paymentIntentId !== null
            ? " (payment {$paymentIntentId})"
            : '';

        Notification::make()
            ->title("Stripe payment needs review for order {$order->order_number}")
            ->body("The customer paid {$paid} by card{$reference}, but {$reason}. The payment was not applied to the order, so refund it in Stripe if it should not count.")
            ->warning()
            ->sendToDatabase(User::query()->owners()->get());
    }
}
