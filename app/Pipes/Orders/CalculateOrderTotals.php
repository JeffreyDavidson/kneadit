<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\DeliveryType;
use App\Exceptions\Orders\NoOrderableItemsException;
use App\Models\Inventory\Product;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Closure;
use Illuminate\Validation\ValidationException;

class CalculateOrderTotals
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $productIds = array_column($payload->data->items, 'product_id');
        $products = Product::query()
            ->select(['id', 'name', 'is_active', 'price'])
            ->findOrFail($productIds)
            ->keyBy('id');

        $unavailable = $products->reject(fn (Product $product): bool => $product->is_active);

        if ($unavailable->count() === $products->count()) {
            throw new NoOrderableItemsException;
        }

        // Some of the cart can still be ordered: name what can't rather than dropping it silently.
        if ($unavailable->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'These items are no longer available: %s. Please remove them from your cart.',
                    $unavailable->implode('name', ', '),
                ),
            ]);
        }

        foreach ($payload->data->items as $item) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                continue;
            }

            $unitPrice = $product->price ?? Money::zero();
            $payload->subtotal = $payload->subtotal->add($unitPrice->multiply($item['quantity']));

            $payload->orderItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
            ];
        }

        if ($payload->orderItems === []) {
            throw new NoOrderableItemsException;
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
