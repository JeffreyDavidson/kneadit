<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private string $baseUrl;

    private ?string $clientId;

    private ?string $clientSecret;

    private ?string $accessToken = null;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->baseUrl = config('services.paypal.sandbox', true)
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    /**
     * Get PayPal access token using client credentials
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)
                ->asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->baseUrl.'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                $this->accessToken = $response->json('access_token');

                return $this->accessToken;
            }

            Log::error('Failed to get PayPal access token', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal authentication error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Create and send PayPal invoice for an order
     */
    public function createAndSendInvoice(Order $order): ?string
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return null;
        }

        $order->load(['customer', 'orderItems.product']);

        $invoiceData = [
            'detail' => [
                'invoice_number' => $order->order_number,
                'reference' => 'Order #'.$order->order_number,
                'invoice_date' => Date::now()->toISOString(),
                'currency_code' => 'USD',
                'note' => 'Thank you for your order with KneadIt Bakery!',
                'terms' => 'Payment due within 30 days.',
                'memo' => 'KneadIt Bakery - Fresh Baked Goods',
            ],
            'invoicer' => [
                'name' => [
                    'given_name' => 'KneadIt',
                    'surname' => 'Bakery',
                ],
                'address' => [
                    'address_line_1' => '123 Baker Street',
                    'admin_area_2' => 'Your City',
                    'admin_area_1' => 'Your State',
                    'postal_code' => '12345',
                    'country_code' => 'US',
                ],
                'email_address' => config('mail.from.address', 'noreply@kneadit.com'),
                'phones' => [
                    [
                        'country_code' => '1',
                        'national_number' => '5551234567',
                        'phone_type' => 'MOBILE',
                    ],
                ],
            ],
            'primary_recipients' => [
                [
                    'billing_info' => [
                        'name' => [
                            'given_name' => explode(' ', $order->customer?->name)[0],
                            'surname' => implode(' ', array_slice(explode(' ', $order->customer?->name), 1)) ?: '',
                        ],
                        'address' => [
                            'address_line_1' => $order->delivery_address ?: $order->customer->address,
                            'admin_area_2' => $order->customer?->city,
                            'admin_area_1' => $order->customer?->state,
                            'postal_code' => $order->customer?->zip,
                            'country_code' => 'US',
                        ],
                        'email_address' => $order->customer?->email,
                        'phones' => $order->customer?->phone ? [
                            [
                                'country_code' => '1',
                                'national_number' => preg_replace('/\D/', '', $order->customer->phone),
                                'phone_type' => 'MOBILE',
                            ],
                        ] : [],
                    ],
                ],
            ],
            'items' => [],
            'configuration' => [
                'partial_payment' => [
                    'allow_partial_payment' => false,
                ],
                'allow_tip' => false,
                'tax_calculated_after_discount' => true,
                'tax_inclusive' => false,
            ],
            'amount' => [
                'currency_code' => 'USD',
            ],
        ];

        // Add order items
        $subtotal = 0;
        foreach ($order->orderItems as $item) {
            $itemTotal = $item->quantity * $item->unit_price;
            $subtotal += $itemTotal;

            $invoiceData['items'][] = [
                'name' => $item->product?->name,
                'description' => $item->product->description ?: $item->product->name,
                'quantity' => (string) $item->quantity,
                'unit_amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($item->unit_price, 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        // Add delivery fee if applicable
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

        // Set totals
        $invoiceData['amount']['breakdown'] = [
            'item_total' => [
                'currency_code' => 'USD',
                'value' => number_format($order->subtotal + $order->delivery_fee, 2, '.', ''),
            ],
        ];

        if ($order->discount_amount > 0) {
            $invoiceData['amount']['breakdown']['discount'] = [
                'invoice_discount' => [
                    'percent' => '0',
                ],
            ];
        }

        try {
            // Create the invoice
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => 'INVOICE-'.$order->order_number.'-'.time(),
            ])->post($this->baseUrl.'/v2/invoicing/invoices', $invoiceData);

            if ($response->successful()) {
                $invoiceId = $response->json('id');

                // Send the invoice
                $sendResponse = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl.'/v2/invoicing/invoices/'.$invoiceId.'/send', [
                    'send_to_invoicer' => true,
                ]);

                if ($sendResponse->successful()) {
                    // Update order with PayPal invoice ID
                    $order->update(['paypal_invoice_id' => $invoiceId]);

                    Log::info('PayPal invoice created and sent', [
                        'order_id' => $order->id,
                        'invoice_id' => $invoiceId,
                    ]);

                    return $invoiceId;
                } else {
                    Log::error('Failed to send PayPal invoice', [
                        'order_id' => $order->id,
                        'invoice_id' => $invoiceId,
                        'response' => $sendResponse->json(),
                    ]);
                }
            } else {
                Log::error('Failed to create PayPal invoice', [
                    'order_id' => $order->id,
                    'response' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PayPal invoice creation error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Get invoice payment status
     */
    public function getInvoiceStatus(string $invoiceId): ?string
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return null;
        }

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl.'/v2/invoicing/invoices/'.$invoiceId);

            if ($response->successful()) {
                return $response->json('status');
            }

            Log::error('Failed to get PayPal invoice status', [
                'invoice_id' => $invoiceId,
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal invoice status check error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Cancel PayPal invoice
     */
    public function cancelInvoice(string $invoiceId): bool
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return false;
        }

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)->withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/v2/invoicing/invoices/'.$invoiceId.'/cancel', [
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
            Log::error('PayPal invoice cancellation error: '.$e->getMessage());
        }

        return false;
    }
}
