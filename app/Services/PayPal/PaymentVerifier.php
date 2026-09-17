<?php

declare(strict_types=1);

namespace App\Services\PayPal;

use App\Services\PayPal\Contracts\PayPalClient;
use Illuminate\Support\Facades\Log;

class PaymentVerifier
{
    public function __construct(
        protected PayPalClient $client,
    ) {}

    public function getInvoiceStatus(string $invoiceId): ?string
    {
        try {
            $response = $this->client->getInvoice($invoiceId);

            if ($response->successful) {
                $status = $response->data['status'] ?? null;

                return is_string($status) ? $status : null;
            }

            Log::error('Failed to get PayPal invoice status', [
                'invoice_id' => $invoiceId,
                'status' => $response->status,
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal invoice status check error', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
        }

        return null;
    }
}
