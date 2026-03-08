<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function updating(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === 'baking') {
            $this->deductIngredients($order);
        }
    }

    protected function deductIngredients(Order $order): void
    {
        $order->loadMissing('orderItems.product.recipes.inventoryIngredients');

        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product;
            if (!$product) continue;

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
    }
}
