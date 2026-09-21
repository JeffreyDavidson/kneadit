<?php

namespace App\Services\Orders;

use App\DataTransferObjects\Inventory\IngredientDemandItem;
use App\Exceptions\Orders\InsufficientStockException;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Services\Inventory\IngredientDemandCalculator;

/**
 * Checks whether an order's projected ingredient consumption fits inside
 * current ingredient stock. Mirrors the recipe walk used by
 * DeductIngredientsForOrder so a check pre-deduction returns the same
 * answer the eventual deduction would.
 *
 * Intentionally does not account for ingredients reserved by other pending
 * orders — placement-time checks have the same shape, so adding cross-order
 * reservation accounting only here would be inconsistent.
 */
class CheckOrderStockAvailability
{
    public function __construct(private readonly IngredientDemandCalculator $calculator) {}

    /**
     * @throws InsufficientStockException when any ingredient's projected
     *                                    demand exceeds its current stock.
     */
    public function __invoke(Order $order): void
    {
        $order->loadMissing('orderItems.product.recipes.inventoryIngredients');

        $items = [];

        foreach ($order->orderItems as $orderItem) {
            if ($orderItem->product instanceof Product) {
                $items[] = new IngredientDemandItem(
                    product: $orderItem->product,
                    quantity: $orderItem->quantity,
                );
            }
        }

        $shortages = $this->calculator->shortages($items);

        throw_if($shortages !== [], InsufficientStockException::class, shortages: $shortages, order: $order);
    }
}
