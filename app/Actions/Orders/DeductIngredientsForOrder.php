<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\DataTransferObjects\Inventory\IngredientShortfall;
use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Orders\Order;

class DeductIngredientsForOrder
{
    public function __construct(
        private readonly AdjustOrderIngredients $adjustOrderIngredients,
    ) {}

    /**
     * @return array<int, IngredientShortfall> Ingredients left below zero by this order.
     */
    public function __invoke(Order $order): array
    {
        return ($this->adjustOrderIngredients)($order, StockAdjustmentType::Usage);
    }
}
