<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Ingredient;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ShoppingListService
{
    /** @return array<int|string, array<string, mixed>> */
    public function generate(bool $includeUpcoming = false, ?string $startDate = null, ?string $endDate = null): array
    {
        $lowStockIngredients = Ingredient::query()->where(function (Builder $q) {
            $q->where('current_stock', '<=', 0)
                ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
        })->with('suppliers')->get();

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

        $orders = Order::query()->whereBetween('pickup_date', [$startDate, $endDate])
            ->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::Delivered])
            ->with('orderItems.product.recipe.inventoryIngredients')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                if ($item->product?->recipe) {
                    foreach ($item->product->recipe->inventoryIngredients as $ingredient) {
                        $needed = ($ingredient->pivot->quantity ?? 0) * $item->quantity;
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
                ->sortBy('pivot.unit_price')
                ->first();

            /** @var object{unit_price: string, minimum_order: string, lead_time_days: int, sku: string}|null $pivot */
            $pivot = $bestSupplier?->pivot;

            $item = [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'current_stock' => $ingredient->current_stock,
                'needed' => round($neededQty, 2),
                'unit_price' => $pivot->unit_price ?? $ingredient->cost_per_unit ?? 0,
                'subtotal' => round($neededQty * (float) ($pivot->unit_price ?? $ingredient->cost_per_unit ?? 0), 2),
                'sku' => $pivot?->sku,
                'minimum_order' => $pivot?->minimum_order,
                'lead_time_days' => $pivot?->lead_time_days,
            ];

            if ($bestSupplier) {
                $grouped[$bestSupplier->id]['supplier'] = [
                    'id' => $bestSupplier->id,
                    'name' => $bestSupplier->name,
                    'email' => $bestSupplier->email,
                    'phone' => $bestSupplier->phone,
                ];
                $grouped[$bestSupplier->id]['items'][] = $item;
                $grouped[$bestSupplier->id]['total'] = array_sum(array_column($grouped[$bestSupplier->id]['items'], 'subtotal'));
            } else {
                $noSupplier[] = $item;
            }
        }

        if (! empty($noSupplier)) {
            $grouped['none'] = [
                'supplier' => ['id' => null, 'name' => 'No Supplier Assigned', 'email' => null, 'phone' => null],
                'items' => $noSupplier,
                'total' => array_sum(array_column($noSupplier, 'subtotal')),
            ];
        }

        return $grouped;
    }
}
