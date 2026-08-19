<?php

namespace App\Services\Inventory;

use App\DataTransferObjects\Inventory\ShoppingListItem;
use App\DataTransferObjects\Inventory\SupplierShoppingList;
use App\DataTransferObjects\Inventory\SupplierSummary;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class ShoppingListService
{
    /** @return Collection<int|string, SupplierShoppingList> */
    public function generate(bool $includeUpcoming = false, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $lowStockIngredients = Ingredient::query()->lowStock()->withActiveSuppliers()->get();

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
                        $quantity = $ingredient->pivot->quantity ?? 0;
                        $quantity = is_int($quantity) || is_float($quantity) || is_string($quantity)
                            ? Number::parseFloat((string) $quantity) ?: 0.0
                            : 0.0;
                        $needed = $quantity * $item->quantity;
                        $needs[$ingredient->id] = ($needs[$ingredient->id] ?? 0) + $needed;
                    }
                }
            }
        }

        return $needs;
    }

    /**
     * @param Collection<int, Ingredient> $lowStockIngredients
     * @param array<int, float> $upcomingNeeds
     * @return Collection<int|string, SupplierShoppingList>
     */
    private function groupBySupplier(Collection $lowStockIngredients, array $upcomingNeeds): Collection
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

            /** @var object{unit_price: ?int, minimum_order: ?int, lead_time_days: int, sku: string}|null $pivot */
            $pivot = $bestSupplier?->pivot;

            // ingredient_supplier.unit_price + .minimum_order are bigint cents
            // (migration 2026_04_22_240000); the pivot has no cast so divide
            // back to dollars at the boundary.
            $pivotUnitPrice = $pivot?->unit_price !== null ? (int) $pivot->unit_price / 100 : null;
            $effectiveUnitPrice = $pivotUnitPrice ?? $ingredient->cost_per_unit?->dollars() ?? 0;

            $item = new ShoppingListItem(
                ingredientId: $ingredient->id,
                name: $ingredient->name,
                unit: $ingredient->unit,
                currentStock: (float) $ingredient->current_stock,
                needed: round($neededQty, 2),
                unitPrice: (float) $effectiveUnitPrice,
                subtotal: round($neededQty * (float) $effectiveUnitPrice, 2),
                sku: $pivot?->sku,
                minimumOrder: $pivot?->minimum_order !== null ? (int) $pivot->minimum_order / 100 : null,
                leadTimeDays: $pivot?->lead_time_days,
            );

            if ($bestSupplier) {
                $grouped[$bestSupplier->id]['supplier'] = new SupplierSummary(
                    id: $bestSupplier->id,
                    name: $bestSupplier->name,
                    email: $bestSupplier->email,
                    phone: $bestSupplier->phone,
                );
                $grouped[$bestSupplier->id]['items'][] = $item;
            } else {
                $noSupplier[] = $item;
            }
        }

        if (! empty($noSupplier)) {
            $grouped['none'] = [
                'supplier' => new SupplierSummary(null, 'No Supplier Assigned', null, null),
                'items' => $noSupplier,
            ];
        }

        return collect($grouped)->map(
            /** @param array{supplier: SupplierSummary, items: list<ShoppingListItem>} $group */
            fn (array $group): SupplierShoppingList => new SupplierShoppingList(
                supplier: $group['supplier'],
                items: collect($group['items']),
            ),
        );
    }
}
