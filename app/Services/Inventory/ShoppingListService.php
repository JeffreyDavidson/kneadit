<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use App\Models\Orders\Order;
use Illuminate\Support\Collection;

class ShoppingListService
{
    public function __construct(private readonly RecipeLineConverter $converter) {}

    /** @return array<int|string, array<string, mixed>> */
    public function generate(bool $includeUpcoming = false, ?string $startDate = null, ?string $endDate = null): array
    {
        $lowStockIngredients = Ingredient::query()->lowStock()->with('suppliers')->get();

        $upcomingNeeds = $this->calculateUpcomingNeeds($includeUpcoming, $startDate, $endDate);

        return $this->groupBySupplier($lowStockIngredients, $upcomingNeeds);
    }

    /** @return array<int, float> */
    private function calculateUpcomingNeeds(bool $includeUpcoming, ?string $startDate, ?string $endDate): array
    {
        $needs = [];

        if (! $includeUpcoming || ! $startDate || ! $endDate) {
            return $needs;
        }

        $orders = Order::query()->whereBetween('delivery_date', [$startDate, $endDate])
            ->outstanding()
            ->with('orderItems.product.recipe.inventoryIngredients')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                if ($item->product?->recipe) {
                    foreach ($item->product->recipe->inventoryIngredients as $ingredient) {
                        $quantity = $this->converter->inStockUnit($item->product->recipe, $ingredient);

                        if ($quantity === null) {
                            continue;
                        }

                        $needed = $quantity * $item->quantity;
                        $needs[$ingredient->id] = ($needs[$ingredient->id] ?? 0) + $needed;
                    }
                }
            }
        }

        return $needs;
    }

    /**
     * @param  Collection<int, Ingredient>  $lowStockIngredients
     * @param  array<int, float>  $upcomingNeeds
     * @return array<int|string, array<string, mixed>>
     */
    private function groupBySupplier(Collection $lowStockIngredients, array $upcomingNeeds): array
    {
        $grouped = [];
        $noSupplier = [];

        foreach ($lowStockIngredients as $ingredient) {
            $neededQty = max(0, ($ingredient->low_stock_threshold * 2) - $ingredient->current_stock);
            $neededQty += $upcomingNeeds[$ingredient->id] ?? 0;

            if ($neededQty <= 0) {
                continue;
            }

            $bestSupplier = $ingredient->suppliers
                ->where('is_active', true)
                ->sortBy(fn (Supplier $supplier): int => $supplier->pivot?->unit_price?->cents() ?? PHP_INT_MAX)
                ->first();

            // The pivot casts unit_price and minimum_order to Money (stored as cents).
            $pivot = $bestSupplier?->pivot;

            $effectiveUnitPrice = $pivot?->unit_price?->dollars() ?? $ingredient->cost_per_unit?->dollars() ?? 0;

            $item = [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'current_stock' => $ingredient->current_stock,
                'needed' => round($neededQty, 2),
                'unit_price' => $effectiveUnitPrice,
                'subtotal' => round($neededQty * (float) $effectiveUnitPrice, 2),
                'sku' => $pivot?->sku,
                'minimum_order' => $pivot?->minimum_order?->dollars(),
                'lead_time_days' => $pivot?->lead_time_days,
            ];

            if ($bestSupplier) {
                $grouped[$bestSupplier->id] ??= [
                    'supplier' => [
                        'id' => $bestSupplier->id,
                        'name' => $bestSupplier->name,
                        'email' => $bestSupplier->email,
                        'phone' => $bestSupplier->phone,
                    ],
                    'items' => [],
                    'total' => 0.0,
                ];

                $grouped[$bestSupplier->id]['items'][] = $item;
                $grouped[$bestSupplier->id]['total'] += $item['subtotal'];
            } else {
                $noSupplier[] = $item;
            }
        }

        if ($noSupplier !== []) {
            $grouped['none'] = [
                'supplier' => ['id' => null, 'name' => 'No Supplier Assigned', 'email' => null, 'phone' => null],
                'items' => $noSupplier,
                'total' => array_sum(array_column($noSupplier, 'subtotal')),
            ];
        }

        return $grouped;
    }
}
