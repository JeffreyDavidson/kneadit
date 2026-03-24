<?php

namespace App\Actions\Orders;

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
                        $qty = $ingredient->pivot->quantity * $orderItem->quantity;
                        $ingredient->adjustStock(
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
