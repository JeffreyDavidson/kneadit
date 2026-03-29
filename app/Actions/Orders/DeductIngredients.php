<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\StockAdjustmentType;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DeductIngredients
{
    public function __construct(
        protected AdjustIngredientStock $adjustStock,
    ) {}

    public function __invoke(Order $order): void
    {
        $order->loadMissing('orderItems.product.recipes.inventoryIngredients');

        DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;

                if (! $product) {
                    continue;
                }

                foreach ($product->recipes as $recipe) {
                    foreach ($recipe->inventoryIngredients as $ingredient) {
                        /** @var object{quantity: string, unit: string} $pivot */
                        $pivot = $ingredient->pivot;
                        $qty = (float) $pivot->quantity * $orderItem->quantity;
                        ($this->adjustStock)(
                            $ingredient,
                            -$qty,
                            StockAdjustmentType::Usage,
                            "Order #{$order->order_number}"
                        );
                    }
                }
            }
        });
    }
}
