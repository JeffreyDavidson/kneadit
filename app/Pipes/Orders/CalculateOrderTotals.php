<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\DeliveryType;
use App\Models\Inventory\Product;
use App\Support\DatabaseValue;
use Closure;
use Illuminate\Support\Arr;

class CalculateOrderTotals
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $productIds = array_column($payload->data->items, 'product_id');
        $products = Product::query()->findOrFail($productIds)->keyBy('id');

        foreach ($payload->data->items as $item) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                continue;
            }
            if (! $product->is_active) {
                continue;
            }

            $unitPrice = $product->price?->dollars() ?? 0.0;
            $lineTotal = $unitPrice * $item['quantity'];
            $payload->subtotal += $lineTotal;

            $payload->orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
            ];
        }

        if (empty($payload->orderItems)) {
            $payload->cancelled = true;

            return $payload;
        }

        if ($payload->data->deliveryType === DeliveryType::Delivery->value) {
            $fees = config('kneadit.delivery_fees', []);
            $fee = is_array($fees) ? Arr::get($fees, $payload->data->deliveryTier) : null;
            $payload->deliveryFee = DatabaseValue::float($fee);
        }

        $payload->tipAmount = max(0.0, $payload->data->tipAmount);

        $payload->total = $payload->subtotal + $payload->deliveryFee + $payload->tipAmount;

        return $next($payload);
    }
}
