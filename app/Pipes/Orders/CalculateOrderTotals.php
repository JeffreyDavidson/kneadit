<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\DeliveryType;
use App\Models\Inventory\Product;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Closure;

class CalculateOrderTotals
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $productIds = array_column($payload->data->items, 'product_id');
        $products = Product::query()
            ->select(['id', 'is_active', 'price'])
            ->findOrFail($productIds)
            ->keyBy('id');

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
            $payload->deliveryFee = Money::fromDollars(
                $this->settings->orders->deliveryFee($payload->data->deliveryTier ?? '', $payload->subtotal->dollars()),
            );
        }

        $payload->tipAmount = Money::fromDollars(max(0.0, $payload->data->tipAmount));

        $payload->recalculateTotal();

        return $next($payload);
    }
}
