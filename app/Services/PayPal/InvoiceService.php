<?php

namespace App\Services\PayPal;

use App\Actions\Orders\RecordPayPalInvoice;
use App\Models\Orders\Order;
use App\Services\PayPal\Contracts\PayPalClient;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        protected PayPalClient $client,
        protected InvoicePayloadBuilder $payloadBuilder,
        protected RecordPayPalInvoice $recordPayPalInvoice,
    ) {}

    public function createAndSend(Order $order): ?string
    {
        $invoiceData = $this->payloadBuilder->build($order);

        try {
            $response = $this->client->createInvoice($invoiceData, "INVOICE-{$order->order_number}-".time());

            if (! $response->successful) {
                Log::error('Failed to create PayPal invoice', [
                    'order_id' => $order->id,
                    'status' => $response->status,
                ]);

                return null;
            }

            $invoiceId = $response->data['id'] ?? null;

            if (! is_string($invoiceId) || $invoiceId === '') {
                Log::error('PayPal invoice response did not contain an invoice ID', [
                    'order_id' => $order->id,
                ]);

                return null;
            }

            $sendResponse = $this->client->sendInvoice($invoiceId);

            if (! $sendResponse->successful) {
                Log::error('Failed to send PayPal invoice', [
                    'order_id' => $order->id,
                    'invoice_id' => $invoiceId,
                    'status' => $sendResponse->status,
                ]);

                return null;
            }

            ($this->recordPayPalInvoice)($order, $invoiceId);

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
        try {
            $response = $this->client->cancelInvoice($invoiceId);

            if ($response->successful) {
                Log::info('PayPal invoice cancelled', ['invoice_id' => $invoiceId]);

                return true;
            }

            Log::error('Failed to cancel PayPal invoice', [
                'invoice_id' => $invoiceId,
                'status' => $response->status,
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal invoice cancellation error', ['invoice_id' => $invoiceId, 'error' => $e->getMessage()]);
        }

        return false;
    }
}
