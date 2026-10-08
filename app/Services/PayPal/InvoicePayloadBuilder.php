<?php

namespace App\Services\PayPal;

use App\Models\Orders\Order;
use App\Services\Settings\SettingsManager;
use App\Services\Settings\TenantSettings;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

class InvoicePayloadBuilder
{
    public function __construct(
        private readonly TenantSettings $settings,
        private readonly SettingsManager $manager,
    ) {}

    /** @return array<string, mixed> */
    public function build(Order $order): array
    {
        $order->loadMissing(['customer', 'orderItems.product']);
        $currency = Config::string('kneadit.paypal.currency', 'USD');

        return [
            'detail' => [
                'invoice_number' => $order->order_number,
                'reference' => "Order #{$order->order_number}",
                'invoice_date' => Date::now()->toISOString(),
                'currency_code' => $currency,
                'note' => "Thank you for your order with {$this->settings->store->name}!",
                'terms' => $this->manager->get('paypal_invoice_terms', 'Payment due within 30 days.'),
                'memo' => "{$this->settings->store->name} - Fresh Baked Goods",
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
            'name' => ['given_name' => $this->settings->store->name, 'surname' => ''],
            'address' => [
                'address_line_1' => $this->settings->store->address ?? '',
                'admin_area_2' => $this->manager->get('store_city', ''),
                'admin_area_1' => $this->manager->get('store_state', ''),
                'postal_code' => $this->manager->get('store_zip', ''),
                'country_code' => 'US',
            ],
            'email_address' => config('mail.from.address', 'noreply@kneadit.com'),
            'phones' => $this->formatPhone($this->settings->store->phone),
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
                'phones' => $this->formatPhone($order->customer?->phone),
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
                    'value' => number_format($item->unit_price->dollars(), 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        if ($order->delivery_fee->isPositive()) {
            $items[] = [
                'name' => 'Delivery Fee',
                'description' => 'Delivery service',
                'quantity' => '1',
                'unit_amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($order->delivery_fee->dollars(), 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        if ($order->tip_amount->isPositive()) {
            $items[] = [
                'name' => 'Tip',
                'description' => 'Tip for the bakery team',
                'quantity' => '1',
                'unit_amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($order->tip_amount->dollars(), 2, '.', ''),
                ],
                'unit_of_measure' => 'QUANTITY',
            ];
        }

        return $items;
    }

    /** @return array<string, mixed> */
    private function buildAmountBreakdown(Order $order, string $currency): array
    {
        $discount = $order->discount_amount->add($order->gift_card_amount);
        $itemTotal = $order->subtotal
            ->add($order->delivery_fee)
            ->add($order->tip_amount);

        $amount = [
            'currency_code' => $currency,
            'value' => number_format($order->total->dollars(), 2, '.', ''),
            'breakdown' => [
                'item_total' => [
                    'currency_code' => $currency,
                    'value' => number_format($itemTotal->dollars(), 2, '.', ''),
                ],
            ],
        ];

        if ($discount->isPositive()) {
            $amount['breakdown']['discount'] = [
                'invoice_discount' => [
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($discount->dollars(), 2, '.', ''),
                    ],
                ],
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

    /**
     * PayPal wants the calling code and the national number apart. A number
     * that can't be read is left off rather than sent with the wrong code.
     *
     * @return array<int, array<string, string>>
     */
    private function formatPhone(?string $phone): array
    {
        $parts = PhoneNumber::callingCodeAndNationalNumber($phone);

        if ($parts === null) {
            return [];
        }

        return [['country_code' => $parts[0], 'national_number' => $parts[1], 'phone_type' => 'MOBILE']];
    }
}
