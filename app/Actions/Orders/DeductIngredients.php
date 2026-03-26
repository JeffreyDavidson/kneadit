<?php

namespace App\Actions\Orders;

use App\Actions\AdjustIngredientStock;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DeductIngredients
{
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
                        app(AdjustIngredientStock::class)(
                            $ingredient,
                            -$qty,
                            'usage',
                            "Order #{$order->order_number}"
                        );
                    }
                }
            }
        });
    }
}
