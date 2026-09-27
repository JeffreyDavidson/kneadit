<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Orders\Order;

class RestockIngredientsForOrder
{
    public function __construct(
        private readonly AdjustOrderIngredients $adjustOrderIngredients,
    ) {}

    public function __invoke(Order $order): void
    {
        ($this->adjustOrderIngredients)($order, StockAdjustmentType::Restock);
    }
}
