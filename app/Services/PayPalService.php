<?php

namespace App\Services;

use App\Models\Order;
use App\Services\PayPal\InvoiceService;
use App\Services\PayPal\PaymentVerifier;

class PayPalService
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected PaymentVerifier $paymentVerifier,
    ) {}

    public function createAndSendInvoice(Order $order): ?string
    {
        return $this->invoiceService->createAndSend($order);
    }

    public function getInvoiceStatus(string $invoiceId): ?string
    {
        return $this->paymentVerifier->getInvoiceStatus($invoiceId);
    }

    public function cancelInvoice(string $invoiceId): bool
    {
        return $this->invoiceService->cancel($invoiceId);
    }
}
