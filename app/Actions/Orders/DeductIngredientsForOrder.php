<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\AdjustIngredientStock;
use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;

class DeductIngredientsForOrder
{
    public function __construct(
        private AdjustIngredientStock $adjustStock,
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
