<?php

namespace App\Services\PayPal;

use App\Models\Orders\Order;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

class InvoicePayloadBuilder
{
    public function __construct(
        private TenantSettings $settings,
        private SettingsManager $manager,
    ) {}

    /** @return array<string, mixed> */
    public function build(Order $order): array
    {
        $order->loadMissing(['customer', 'orderItems.product']);
        $currency = config('kneadit.paypal.currency', 'USD');

        return [
            'detail' => [
                'invoice_number' => $order->order_number,
                'reference' => "Order #{$order->order_number}",
                'invoice_date' => Date::now()->toISOString(),
                'currency_code' => $currency,
                'note' => "Thank you for your order with {$this->settings->storeName}!",
                'terms' => $this->manager->get('paypal_invoice_terms', 'Payment due within 30 days.'),
                'memo' => "{$this->settings->storeName} - Fresh Baked Goods",
            ],
            'invoicer' => $this->buildInvoicer(),
            'primary_recipients' => [$this->buildRecipient($order)],
            'items' => $this->buildItems($order, $currency),
            'configuration' => [
                'partial_payment' => ['allow_partial_payment' => false],
                'allow_tip' => false,
                'tax_calculated_after_discount' => true,
                'tax_inclusive' => false,
            ],
            'amount' => $this->buildAmountBreakdown($order, $currency),
        ];
    }

    /** @return array<string, mixed> */
    private function buildInvoicer(): array
    {
        return [
            'name' => ['given_name' => $this->settings->storeName, 'surname' => ''],
            'address' => [
                'address_line_1' => $this->settings->storeAddress ?? '',
                'admin_area_2' => $this->manager->get('store_city', ''),
                'admin_area_1' => $this->manager->get('store_state', ''),
                'postal_code' => $this->manager->get('store_zip', ''),
                'country_code' => 'US',
            ],
            'email_address' => config('mail.from.address', 'noreply@kneadit.com'),
            'phones' => [['country_code' => '1', 'national_number' => $this->settings->storePhone ?? '', 'phone_type' => 'MOBILE']],
        ];
    }

    /** @return array<string, mixed> */
    private function buildRecipient(Order $order): array
    {
        [$givenName, $surname] = $this->parseCustomerName($order->customer->name ?? '');

        return [
            'billing_info' => [
                'name' => ['given_name' => $givenName, 'surname' => $surname],
                'address' => [
                    'address_line_1' => $order->delivery_address ?: $order->customer?->address,
                    'admin_area_2' => $order->customer?->city,
                    'admin_area_1' => $order->customer?->state,
                    'postal_code' => $order->customer?->zip,
                    'country_code' => 'US',
                ],
                'email_address' => $order->customer?->email,
                'phones' => $this->formatCustomerPhone($order->customer?->phone),
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function buildItems(Order $order, string $currency): array
    {
        $items = [];

        foreach ($order->orderItems as $item) {
            $items[] = [
                'name' => $item->product?->name,
                'description' => $item->product?->description ?: $item->product->name ?? 'Item',
                'quantity' => (string) $item->quantity,
                'unit_amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($item->unit_price, 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        if ($order->delivery_fee > 0) {
            $items[] = [
                'name' => 'Delivery Fee',
                'description' => 'Delivery service',
                'quantity' => '1',
                'unit_amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($order->delivery_fee, 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        return $items;
    }

    /** @return array<string, mixed> */
    private function buildAmountBreakdown(Order $order, string $currency): array
    {
        $amount = [
            'currency_code' => $currency,
            'breakdown' => [
                'item_total' => [
                    'currency_code' => $currency,
                    'value' => number_format($order->subtotal + $order->delivery_fee, 2, '.', ''),
                ],
            ],
        ];

        if ($order->discount_amount > 0) {
            $amount['breakdown']['discount'] = [
                'invoice_discount' => ['percent' => '0'],
            ];
        }

        return $amount;
    }

    /** @return array{0: string, 1: string} */
    private function parseCustomerName(string $name): array
    {
        $parts = explode(' ', trim($name));

        if (count($parts) <= 1) {
            return [$name, ''];
        }

        return [$parts[0], implode(' ', array_slice($parts, 1))];
    }

    /** @return array<int, array<string, string>> */
    private function formatCustomerPhone(?string $phone): array
    {
        if (! $phone) {
            return [];
        }

        return [['country_code' => '1', 'national_number' => (string) preg_replace('/\D/', '', $phone), 'phone_type' => 'MOBILE']];
    }
}
