<?php

namespace App\Pipes\Orders;

use App\DataTransferObjects\Inventory\IngredientDemandItem;
use App\Exceptions\Orders\InsufficientStockException;
use App\Models\Inventory\Product;
use App\Services\Inventory\IngredientDemandCalculator;
use Closure;

/**
 * Reject placement when the order's projected ingredient consumption
 * exceeds current stock. Mirrors CheckOrderStockAvailability (used by
 * ModifyOrder) for the placement path where there's no persisted Order
 * yet — walks the items in the pipeline data instead.
 */
class ValidateStockAvailability
{
    public function __construct(private readonly IngredientDemandCalculator $calculator) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $items = $payload->data->items;

        if ($items === []) {
            return $next($payload);
        }

        $productIds = array_unique(array_map(fn (array $item) => (int) $item['product_id'], $items));

        /** @var array<int, Product> $products */
        $products = Product::query()
            ->with('recipes.inventoryIngredients')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id')
            ->all();

        $demandItems = [];

        foreach ($items as $item) {
            $product = $products[(int) $item['product_id']] ?? null;

            if ($product) {
                $demandItems[] = new IngredientDemandItem($product, (int) $item['quantity']);
            }
        }

        $shortages = $this->calculator->shortages($demandItems);

        throw_if($shortages !== [], InsufficientStockException::class, shortages: $shortages);

        return $next($payload);
    }
}
