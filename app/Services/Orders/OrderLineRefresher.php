<?php

namespace App\Services\Orders;

use App\DataTransferObjects\Orders\RefreshedOrderLines;
use App\Models\Inventory\Product;
use App\Models\Orders\CartItem;
use App\Models\Orders\OrderItem;

/**
 * Brings the lines of an earlier order or a saved cart up to date before they
 * go back on the order form: each line takes the product's current price, and
 * lines whose product is gone or inactive are dropped and reported by name, the
 * same products `CalculateOrderTotals` would refuse at checkout.
 */
final readonly class OrderLineRefresher
{
    /**
     * The lines must have their `product` relation loaded.
     *
     * @param  iterable<OrderItem|CartItem>  $lines
     */
    public function refresh(iterable $lines): RefreshedOrderLines
    {
        $items = [];
        $removedNames = [];

        foreach ($lines as $line) {
            $product = $line->product;

            if (! $product instanceof Product || ! $product->is_active) {
                $removedNames[] = $product->name
                    ?? ($line instanceof OrderItem ? $line->name : null)
                    ?? 'An item that is no longer on the menu';

                continue;
            }

            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price?->dollars() ?? 0.0,
                'quantity' => $line->quantity,
            ];
        }

        return new RefreshedOrderLines($items, $removedNames);
    }
}
