<?php

namespace App\Services\PayPal;

use App\Actions\Orders\RecordPayPalInvoice;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        protected TokenManager $tokenManager,
        protected InvoicePayloadBuilder $payloadBuilder,
    ) {}

    public function createAndSend(Order $order): ?string
    {
        $accessToken = $this->tokenManager->getAccessToken();
        if (! $accessToken) {
            return null;
        }

        $baseUrl = $this->tokenManager->getBaseUrl();
        $invoiceData = $this->payloadBuilder->build($order);

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => "INVOICE-{$order->order_number}-" . time(),
            ])->post("{$baseUrl}/v2/invoicing/invoices", $invoiceData);

            if (! $response->successful()) {
                Log::error('Failed to create PayPal invoice', [
                    'order_id' => $order->id,
                    'response' => $response->json(),
                ]);

                return null;
            }

            $invoiceId = $response->json('id');

            $sendResponse = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/v2/invoicing/invoices/{$invoiceId}/send", [
                'send_to_invoicer' => true,
            ]);

            if (! $sendResponse->successful()) {
                Log::error('Failed to send PayPal invoice', [
                    'order_id' => $order->id,
                    'invoice_id' => $invoiceId,
                    'response' => $sendResponse->json(),
                ]);

                return null;
            }

            app(RecordPayPalInvoice::class)($order, $invoiceId);

            Log::info('PayPal invoice created and sent', [
                'order_id' => $order->id,
                'invoice_id' => $invoiceId,
            ]);

            return $invoiceId;
        } catch (\Exception $e) {
            Log::error('PayPal invoice creation error', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public function cancel(string $invoiceId): bool
    {
        $accessToken = $this->tokenManager->getAccessToken();
        if (! $accessToken) {
            return false;
        }

        $baseUrl = $this->tokenManager->getBaseUrl();

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->post("{$baseUrl}/v2/invoicing/invoices/{$invoiceId}/cancel", [
                'subject' => 'Invoice cancelled',
                'note' => 'This invoice has been cancelled.',
                'send_to_invoicer' => true,
                'send_to_recipient' => true,
            ]);

            if ($response->successful()) {
                Log::info('PayPal invoice cancelled', ['invoice_id' => $invoiceId]);

                return true;
            }

            Log::error('Failed to cancel PayPal invoice', [
                'invoice_id' => $invoiceId,
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal invoice cancellation error', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
        }

        return false;
    }
}
