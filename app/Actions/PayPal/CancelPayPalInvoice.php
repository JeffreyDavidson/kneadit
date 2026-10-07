<?php

declare(strict_types=1);

namespace App\Actions\PayPal;

use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Services\PayPal\InvoiceService;

/**
 * Cancels an unpaid order's open PayPal invoice so the customer can't pay it after
 * the order is gone.
 *
 * InvoiceService::cancel() logs and swallows PayPal errors (an invoice that was paid
 * in the meantime can't be cancelled), so this never fails the caller. If the customer
 * did pay, the hourly invoice check reports it to the baker
 * (ReportLatePayPalPayment). The stored invoice id is kept so that payment can still
 * be matched to its order.
 */
class CancelPayPalInvoice
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function __invoke(Order $order): void
    {
        if ($order->paypal_invoice_id === null || $order->payment_status !== PaymentStatus::Unpaid) {
            return;
        }

        $this->invoiceService->cancel($order->paypal_invoice_id);
    }
}
