<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdjustOrderIngredients
{
    public function __construct(
        private readonly AdjustIngredientStock $adjustStock,
    ) {}

    public function __invoke(Order $order, StockAdjustmentType $type): void
    {
        [$direction, $notes] = match ($type) {
            StockAdjustmentType::Usage => [-1, "Order #{$order->order_number}"],
            StockAdjustmentType::Restock => [1, "Order #{$order->order_number} cancelled"],
            default => throw new InvalidArgumentException('Order ingredient adjustments must be usage or restock.'),
        };

        $this->adjust($order, $direction, $type, $notes);
    }

    private function adjust(
        Order $order,
        int $direction,
        StockAdjustmentType $type,
        string $notes,
    ): void {
        $order->loadMissing('orderItems.product.recipes.inventoryIngredients');

        DB::transaction(function () use ($direction, $notes, $order, $type): void {
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;

                if (! $product) {
                    continue;
                }

                foreach ($product->recipes as $recipe) {
                    foreach ($recipe->inventoryIngredients as $ingredient) {
                        /** @var object{quantity: string, unit: string} $pivot */
                        $pivot = $ingredient->pivot;
                        $quantity = $direction * (float) $pivot->quantity * $orderItem->quantity;

                        ($this->adjustStock)($ingredient, $quantity, $type, $notes);
                    }
                }
            }
        });
    }
}
