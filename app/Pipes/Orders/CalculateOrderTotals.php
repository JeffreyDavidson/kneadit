<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\DeliveryType;
use App\Models\Inventory\Product;
use App\ValueObjects\Money;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

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

            $unitPrice = $product->price ?? Money::zero();
            $payload->subtotal = $payload->subtotal->add($unitPrice->multiply($item['quantity']));

            $payload->orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
            ];
        }

        if ($payload->orderItems === []) {
            $payload->cancelled = true;

            return $payload;
        }

        if ($payload->data->deliveryType === DeliveryType::Delivery->value) {
            $payload->deliveryFee = Money::fromDollars(Arr::float(
                Config::array('kneadit.delivery_fees', []),
                $payload->data->deliveryTier,
                0.0,
            ));
        }

        $payload->tipAmount = Money::fromDollars(max(0.0, $payload->data->tipAmount));

        $payload->recalculateTotal();

        return $next($payload);
    }
}
