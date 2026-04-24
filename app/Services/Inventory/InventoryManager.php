<?php

namespace App\Services\Inventory;

use App\Actions\Orders\DeductIngredientsForOrder;
use App\Actions\Orders\RestockIngredientsForOrder;
use App\Exceptions\Orders\CapacityExceededException;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class InventoryManager
{
    public function __construct(
        private CapacityCalculator $capacityCalculator,
        private DeductIngredientsForOrder $deductIngredients,
        private RestockIngredientsForOrder $restockIngredients,
    ) {}

    /**
     * Guard that a date can accept another order.
     * Throws if blocked, closed, or at capacity.
     *
     * @throws CapacityExceededException
     */
    public function guardCapacity(Carbon|string $date): void
    {
        $date = Date::parse($date);

        if (! $this->capacityCalculator->isAvailable($date)) {
            throw new CapacityExceededException(
                $date,
                $this->capacityCalculator->getMaxOrders($date),
            );
        }
    }

    /**
     * Deduct all ingredients for an order's items.
     * Walks order -> items -> products -> recipes -> ingredients
     * in a DB transaction, creating StockAdjustment audit records.
     */
    public function deductForOrder(Order $order): void
    {
        ($this->deductIngredients)($order);
    }

    /**
     * Restock all ingredients an order previously consumed. Used when an order
     * is cancelled after the Baking transition has already deducted its stock.
     * Creates positive-quantity StockAdjustment audit records of type Restock.
     */
    public function restockForOrder(Order $order): void
    {
        ($this->restockIngredients)($order);
    }
}
