<?php

declare(strict_types=1);

namespace App\Services\PayPal\Contracts;

use App\DataTransferObjects\PayPal\PayPalResponse;

interface PayPalClient
{
    public function getInvoice(string $invoiceId): PayPalResponse;

    /** @param array<string, mixed> $invoiceData */
    public function createInvoice(array $invoiceData, string $requestId): PayPalResponse;

    public function sendInvoice(string $invoiceId): PayPalResponse;

    public function cancelInvoice(string $invoiceId): PayPalResponse;
}
