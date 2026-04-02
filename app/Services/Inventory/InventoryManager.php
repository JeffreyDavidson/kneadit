<?php

namespace App\Services\Inventory;

use App\Exceptions\Orders\CapacityExceededException;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class InventoryManager
{
    public function __construct(
        private CapacityCalculator $capacityCalculator,
        private IngredientDeductor $ingredientDeductor,
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
     * Non-throwing check for read contexts (calendar UI, API).
     */
    public function canAcceptOrder(Carbon|string $date): bool
    {
        return $this->capacityCalculator->isAvailable($date);
    }

    /**
     * How many more orders can this date take?
     */
    public function remainingSlots(Carbon|string $date): int
    {
        return $this->capacityCalculator->remainingSlots($date);
    }

    /**
     * Deduct all ingredients for an order's items.
     * Walks order -> items -> products -> recipes -> ingredients
     * in a DB transaction, creating StockAdjustment audit records.
     */
    public function deductForOrder(Order $order): void
    {
        $this->ingredientDeductor->deductForOrder($order);
    }
}
