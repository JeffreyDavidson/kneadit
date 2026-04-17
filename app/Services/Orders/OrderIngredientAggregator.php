<?php

namespace App\Services\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrderIngredientAggregator
{
    /**
     * @return Collection<int, array{name: string, quantity: float, unit: string, in_stock: float|null, stock_unit: string|null, needs_purchase: bool, deficit: float}>
     */
    public function generate(string $startDate, string $endDate): Collection
    {
        $orderItems = $this->fetchOrderItems($startDate, $endDate);
        $aggregated = $this->aggregateIngredients($orderItems);

        return $this->crossReferenceInventory($aggregated)
            ->sortBy('name')
            ->values();
    }

    /** @return Collection<int, OrderItem> */
    private function fetchOrderItems(string $startDate, string $endDate): Collection
    {
        return Order::query()
            ->with(['orderItems.product.recipes'])
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->whereIn('status', [OrderStatus::Confirmed, OrderStatus::Baking])
            ->get()
            ->flatMap(fn (Order $order) => $order->orderItems);
    }

    /**
     * @param Collection<int, OrderItem> $orderItems
     * @return Collection<string, array{name: string, quantity: float, unit: string}>
     */
    private function aggregateIngredients(Collection $orderItems): Collection
    {
        $aggregated = collect();

        foreach ($orderItems as $orderItem) {
            $product = $orderItem->product;
            $quantity = $orderItem->quantity;

            foreach (($product->recipes ?? collect()) as $recipe) {
                if (! $recipe->ingredients) {
                    continue;
                }

                foreach ($recipe->ingredients as $ingredient) {
                    $name = $ingredient['name'] ?? '';
                    $ingredientQuantity = $ingredient['quantity'] ?? 0;
                    $unit = $ingredient['unit'] ?? '';

                    if (! $name) {
                        continue;
                    }

                    $key = "{$name}|{$unit}";
                    $totalQuantity = $ingredientQuantity * $quantity;

                    if ($aggregated->has($key)) {
                        $aggregated[$key]['quantity'] += $totalQuantity;
                    } else {
                        $aggregated[$key] = [
                            'name' => $name,
                            'quantity' => $totalQuantity,
                            'unit' => $unit,
                        ];
                    }
                }
            }
        }

        return $aggregated;
    }

    /**
     * @param Collection<string, array{name: string, quantity: float, unit: string}> $aggregated
     * @return Collection<string, array{name: string, quantity: float, unit: string, in_stock: float|null, stock_unit: string|null, needs_purchase: bool, deficit: float}>
     */
    private function crossReferenceInventory(Collection $aggregated): Collection
    {
        $inventoryIngredients = Ingredient::all()
            ->keyBy(fn (Ingredient $i) => Str::lower($i->name));

        return $aggregated->map(function (array $item) use ($inventoryIngredients) {
            $tracked = $inventoryIngredients->get(Str::lower($item['name']));
            $currentStock = $tracked ? (float) $tracked->current_stock : null;

            $item['in_stock'] = $currentStock;
            $item['stock_unit'] = $tracked?->unit;
            $item['needs_purchase'] = $tracked ? $item['quantity'] > $currentStock : true;
            $item['deficit'] = $tracked
                ? (float) max(0, $item['quantity'] - $currentStock)
                : $item['quantity'];

            return $item;
        });
    }
}
