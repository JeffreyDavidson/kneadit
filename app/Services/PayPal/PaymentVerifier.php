<?php

namespace App\Services\PayPal;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentVerifier
{
    public function __construct(
        protected TokenManager $tokenManager,
    ) {}

    public function getInvoiceStatus(string $invoiceId): ?string
    {
        $accessToken = $this->tokenManager->getAccessToken();
        if (! $accessToken) {
            return null;
        }

        $baseUrl = $this->tokenManager->getBaseUrl();

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->get("{$baseUrl}/v2/invoicing/invoices/{$invoiceId}");

            if ($response->successful()) {
                $status = $response->json('status');

                return is_string($status) ? $status : null;
            }

            Log::error('Failed to get PayPal invoice status', [
                'invoice_id' => $invoiceId,
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal invoice status check error', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
        }

        return null;
    }
}
