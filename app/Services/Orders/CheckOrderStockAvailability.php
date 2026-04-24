<?php

namespace App\Services\Orders;

use App\Exceptions\Orders\InsufficientStockException;
use App\Models\Orders\Order;

/**
 * Checks whether an order's projected ingredient consumption fits inside
 * current ingredient stock. Mirrors the recipe walk used by
 * DeductIngredientsForOrder so a check pre-deduction returns the same
 * answer the eventual deduction would.
 *
 * Intentionally does not account for ingredients reserved by other pending
 * orders — placement-time checks have the same shape, so adding cross-order
 * reservation accounting only here would be inconsistent.
 */
class CheckOrderStockAvailability
{
    /**
     * @throws InsufficientStockException when any ingredient's projected
     *                                    demand exceeds its current stock.
     */
    public function __invoke(Order $order): void
    {
        $order->loadMissing('orderItems.product.recipes.inventoryIngredients');

        /** @var array<int, array{name: string, demand: float, available: float}> $byIngredientId */
        $byIngredientId = [];

        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product;

            if (! $product) {
                continue;
            }

            foreach ($product->recipes as $recipe) {
                foreach ($recipe->inventoryIngredients as $ingredient) {
                    /** @var object{quantity: string, unit: string} $pivot */
                    $pivot = $ingredient->pivot;
                    $draw = (float) $pivot->quantity * $orderItem->quantity;

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

        if ($shortages !== []) {
            throw new InsufficientStockException(shortages: $shortages, order: $order);
        }
    }
}
