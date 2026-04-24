<?php

namespace App\Pipes\Orders;

use App\Exceptions\Orders\InsufficientStockException;
use App\Models\Inventory\Product;
use Closure;

/**
 * Reject placement when the order's projected ingredient consumption
 * exceeds current stock. Mirrors CheckOrderStockAvailability (used by
 * ModifyOrder) for the placement path where there's no persisted Order
 * yet — walks the items in the pipeline data instead.
 */
class ValidateStockAvailability
{
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

        /** @var array<int, array{name: string, demand: float, available: float}> $byIngredientId */
        $byIngredientId = [];

        foreach ($items as $item) {
            $product = $products[(int) $item['product_id']] ?? null;

            if (! $product) {
                continue;
            }

            $quantity = (int) $item['quantity'];

            foreach ($product->recipes as $recipe) {
                foreach ($recipe->inventoryIngredients as $ingredient) {
                    /** @var object{quantity: string, unit: string} $pivot */
                    $pivot = $ingredient->pivot;
                    $draw = (float) $pivot->quantity * $quantity;

                    $existing = $byIngredientId[$ingredient->id] ?? null;
                    $byIngredientId[$ingredient->id] = [
                        'name' => $ingredient->name,
                        'demand' => ($existing['demand'] ?? 0.0) + $draw,
                        'available' => (float) $ingredient->current_stock,
                    ];
                }
            }
        }

        $shortages = [];
        foreach ($byIngredientId as $row) {
            if ($row['demand'] > $row['available']) {
                $shortages[] = $row['name'];
            }
        }

        throw_if($shortages !== [], InsufficientStockException::class, shortages: $shortages);

        return $next($payload);
    }
}
