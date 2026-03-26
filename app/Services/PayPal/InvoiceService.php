<?php

namespace App\Services\PayPal;

use App\Models\Order;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        protected TokenManager $tokenManager,
    ) {}

    public function createAndSend(Order $order): ?string
    {
        $accessToken = $this->tokenManager->getAccessToken();
        if (! $accessToken) {
            return null;
        }

        $order->load(['customer', 'orderItems.product']);
        $baseUrl = $this->tokenManager->getBaseUrl();
        $invoiceData = $this->buildInvoicePayload($order);

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

            $order->update(['paypal_invoice_id' => $invoiceId]);

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

    /** @return array<string, mixed> */
    private function buildInvoicePayload(Order $order): array
    {
        $invoiceData = [
            'detail' => [
                'invoice_number' => $order->order_number,
                'reference' => "Order #{$order->order_number}",
                'invoice_date' => Date::now()->toISOString(),
                'currency_code' => 'USD',
                'note' => 'Thank you for your order with KneadIt Bakery!',
                'terms' => 'Payment due within 30 days.',
                'memo' => 'KneadIt Bakery - Fresh Baked Goods',
            ],
            'invoicer' => [
                'name' => ['given_name' => 'KneadIt', 'surname' => 'Bakery'],
                'address' => [
                    'address_line_1' => '123 Baker Street',
                    'admin_area_2' => 'Your City',
                    'admin_area_1' => 'Your State',
                    'postal_code' => '12345',
                    'country_code' => 'US',
                ],
                'email_address' => config('mail.from.address', 'noreply@kneadit.com'),
                'phones' => [['country_code' => '1', 'national_number' => '5551234567', 'phone_type' => 'MOBILE']],
            ],
            'primary_recipients' => [[
                'billing_info' => [
                    'name' => [
                        'given_name' => explode(' ', $order->customer->name ?? '')[0],
                        'surname' => implode(' ', array_slice(explode(' ', $order->customer->name ?? ''), 1)) ?: '',
                    ],
                    'address' => [
                        'address_line_1' => $order->delivery_address ?: $order->customer?->address,
                        'admin_area_2' => $order->customer?->city,
                        'admin_area_1' => $order->customer?->state,
                        'postal_code' => $order->customer?->zip,
                        'country_code' => 'US',
                    ],
                    'email_address' => $order->customer?->email,
                    'phones' => $order->customer?->phone ? [
                        ['country_code' => '1', 'national_number' => preg_replace('/\D/', '', $order->customer->phone), 'phone_type' => 'MOBILE'],
                    ] : [],
                ],
            ]],
            'items' => [],
            'configuration' => [
                'partial_payment' => ['allow_partial_payment' => false],
                'allow_tip' => false,
                'tax_calculated_after_discount' => true,
                'tax_inclusive' => false,
            ],
            'amount' => ['currency_code' => 'USD'],
        ];

        foreach ($order->orderItems as $item) {
            $invoiceData['items'][] = [
                'name' => $item->product?->name,
                'description' => $item->product?->description ?: $item->product->name ?? 'Item',
                'quantity' => (string) $item->quantity,
                'unit_amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($item->unit_price, 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        if ($order->delivery_fee > 0) {
            $invoiceData['items'][] = [
                'name' => 'Delivery Fee',
                'description' => 'Delivery service',
                'quantity' => '1',
                'unit_amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($order->delivery_fee, 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        $invoiceData['amount']['breakdown'] = [
            'item_total' => [
                'currency_code' => 'USD',
                'value' => number_format($order->subtotal + $order->delivery_fee, 2, '.', ''),
            ],
        ];

        if ($order->discount_amount > 0) {
            $invoiceData['amount']['breakdown']['discount'] = [
                'invoice_discount' => ['percent' => '0'],
            ];
        }

        return $invoiceData;
    }
}
