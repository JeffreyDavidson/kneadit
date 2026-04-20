<?php

namespace App\Services\Stripe;

use App\Models\Orders\Order;

class StripeSessionPayloadBuilder
{
    /**
     * Build the full Stripe Checkout Session params for an order.
     *
     * @param array<int, array<string, string>> $discounts
     * @return array<string, mixed>
     */
    public function build(Order $order, string $tenantId, string $successUrl, string $cancelUrl, array $discounts = []): array
    {
        $metadata = $this->metadata($order, $tenantId);

        $params = [
            'mode' => 'payment',
            'line_items' => $this->lineItems($order),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $order->customer?->email,
            'metadata' => $metadata,
            'payment_intent_data' => [
                'metadata' => $metadata,
            ],
        ];

        if (! empty($discounts)) {
            $params['discounts'] = $discounts;
        }

        return $params;
    }

    /** @return array<int, array<string, mixed>> */
    public function lineItems(Order $order): array
    {
        $currency = config('cashier.currency', 'usd');
        $lineItems = [];

        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => $product ? $product->name : 'Item',
                        'description' => $item->special_instructions ?: null,
                    ],
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

    /** @return array<string, mixed> */
    private function metadata(Order $order, string $tenantId): array
    {
        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'tenant_id' => $tenantId,
        ];
    }
}
