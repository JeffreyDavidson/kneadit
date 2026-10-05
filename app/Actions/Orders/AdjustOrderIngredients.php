<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\AdjustIngredientStock;
use App\DataTransferObjects\Inventory\IngredientShortfall;
use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Inventory\StockAdjustment;
use App\Models\Orders\Order;
use App\Services\Inventory\RecipeLineConverter;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Moves ingredient stock for an order. Usage takes what the current recipes call
 * for and always goes through, even if that leaves stock below zero; restock puts
 * back what that order's recorded usage rows took, whatever the recipes say now.
 */
class AdjustOrderIngredients
{
    public function __construct(
        private readonly AdjustIngredientStock $adjustStock,
        private readonly RecipeLineConverter $converter,
    ) {}

    /**
     * @return array<int, IngredientShortfall> Ingredients a usage left below zero; always empty for a restock.
     */
    public function __invoke(Order $order, StockAdjustmentType $type): array
    {
        return match ($type) {
            StockAdjustmentType::Usage => $this->use($order),
            StockAdjustmentType::Restock => $this->restock($order),
            default => throw new InvalidArgumentException('Order ingredient adjustments must be usage or restock.'),
        };
    }

    /**
     * @return array<int, IngredientShortfall>
     */
    private function use(Order $order): array
    {
        $order->loadMissing('orderItems.product.recipes.inventoryIngredients');

        return DB::transaction(function () use ($order): array {
            $shortfalls = [];

            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;

                if (! $product) {
                    continue;
                }

                foreach ($product->recipes as $recipe) {
                    foreach ($recipe->inventoryIngredients as $ingredient) {
                        $perUnit = $this->converter->inStockUnit($recipe, $ingredient);

                        if ($perUnit === null) {
                            continue;
                        }

                        // The stock column is decimal(12,4), so round the converted amount at write.
                        $quantity = round(-1 * $perUnit * $orderItem->quantity, 4);

                        $resulting = round(($this->adjustStock)($ingredient, $quantity, StockAdjustmentType::Usage, "Order #{$order->order_number}", $order, allowNegative: true), 4);

                        unset($shortfalls[$ingredient->id]);

                        if ($resulting < 0) {
                            $shortfalls[$ingredient->id] = new IngredientShortfall($ingredient, abs($resulting));
                        }
                    }
                }
            }

            return array_values($shortfalls);
        });
    }

    /**
     * Net the order's usage against anything already restocked, so a repeat call finds nothing left to put back.
     *
     * @return array<int, IngredientShortfall>
     */
    private function restock(Order $order): array
    {
        $adjustments = StockAdjustment::query()
            ->with('ingredient')
            ->where('order_id', $order->id)
            ->whereIn('type', [StockAdjustmentType::Usage, StockAdjustmentType::Restock])
            ->get()
            ->groupBy('ingredient_id');

        DB::transaction(function () use ($adjustments, $order): void {
            foreach ($adjustments as $rows) {
                $net = round($rows->sum(fn (StockAdjustment $row): float => (float) $row->quantity), 4);
                $ingredient = $rows->first()?->ingredient;

                if ($net >= 0 || ! $ingredient) {
                    continue;
                }

                ($this->adjustStock)($ingredient, -$net, StockAdjustmentType::Restock, "Order #{$order->order_number} cancelled", $order);
            }
        });

        return [];
    }
}
