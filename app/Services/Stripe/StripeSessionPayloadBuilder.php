<?php

namespace App\Services\Stripe;

use App\Models\Orders\Order;
use LogicException;

/** @phpstan-type StripeLineItem array{price_data: array{currency: string, product_data: array{name: string, description?: string}, unit_amount: int}, quantity: int} */
class StripeSessionPayloadBuilder
{
    /**
     * Build the full Stripe Checkout Session params for an order.
     *
     * @param list<array{coupon: string}> $discounts
     * @return array{mode: string, line_items: list<StripeLineItem>, success_url: string, cancel_url: string, customer_email?: string, metadata: array<string, string>, payment_intent_data: array{metadata: array<string, string>}, discounts?: list<array{coupon: string}>}
     */
    public function build(Order $order, string $tenantId, string $successUrl, string $cancelUrl, array $discounts = []): array
    {
        $metadata = $this->metadata($order, $tenantId);

        $params = [
            'mode' => 'payment',
            'line_items' => $this->lineItems($order),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
            'payment_intent_data' => [
                'metadata' => $metadata,
            ],
        ];

        $customerEmail = $order->customer?->email;

        if (is_string($customerEmail) && $customerEmail !== '') {
            $params['customer_email'] = $customerEmail;
        }

        if (! empty($discounts)) {
            $params['discounts'] = $discounts;
        }

        return $params;
    }

    /** @return list<StripeLineItem> */
    public function lineItems(Order $order): array
    {
        $configuredCurrency = config('cashier.currency', 'usd');
        $currency = is_string($configuredCurrency) ? $configuredCurrency : 'usd';
        $lineItems = [];

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $productData = [
                'name' => $product ? $product->name : 'Item',
            ];

            if ($item->special_instructions !== null && $item->special_instructions !== '') {
                $productData['description'] = $item->special_instructions;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => $productData,
                    'unit_amount' => $item->unit_price->cents(),
                ],
                'quantity' => $item->quantity,
            ];
        }

        if ($order->delivery_fee->isPositive()) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => 'Delivery Fee'],
                    'unit_amount' => $order->delivery_fee->cents(),
                ],
                'quantity' => 1,
            ];
        }

        return $lineItems;
    }

    /** @return array<string, string> */
    private function metadata(Order $order, string $tenantId): array
    {
        $orderId = $order->getKey();
        $orderNumber = $order->order_number;

        if (! is_int($orderId) && ! is_string($orderId)) {
            throw new LogicException('A persisted order with an order number is required for Stripe checkout.');
        }

        return [
            'order_id' => "{$orderId}",
            'order_number' => $orderNumber,
            'tenant_id' => $tenantId,
        ];
    }
}
