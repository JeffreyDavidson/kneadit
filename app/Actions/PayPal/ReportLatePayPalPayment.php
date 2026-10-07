<?php

declare(strict_types=1);

namespace App\Actions\PayPal;

use App\Models\Orders\Order;
use App\Models\Staff\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Handles a PayPal invoice that was paid after its order was cancelled.
 *
 * The order is left exactly as it is: it stays cancelled and unpaid, because the
 * money has to be handed back through PayPal. The baker is told once per invoice,
 * with the invoice id so the payment can be found and refunded in PayPal. The hourly
 * check keeps seeing the paid invoice, so the first report claims it.
 */
class ReportLatePayPalPayment
{
    public function __invoke(Order $order, string $invoiceId): void
    {
        $firstReport = Cache::add("order-late-paypal-payment:{$invoiceId}", true, now()->addDays(60));

        if (! $firstReport) {
            return;
        }

        Log::warning('PayPal payment arrived for a cancelled order', [
            'order' => $order->order_number,
            'invoice_id' => $invoiceId,
        ]);

        Notification::make()
            ->title("PayPal payment needs review for order {$order->order_number}")
            ->body("The customer paid PayPal invoice {$invoiceId}, but the order was cancelled. The payment was not applied to the order, so refund it in PayPal.")
            ->warning()
            ->sendToDatabase(User::query()->owners()->get());
    }
}
