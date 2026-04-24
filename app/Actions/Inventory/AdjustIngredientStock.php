<?php

namespace App\Actions\Inventory;

use App\Enums\Inventory\StockAdjustmentType;
use App\Exceptions\Inventory\StockWouldGoNegativeException;
use App\Models\Inventory\Ingredient;

class AdjustIngredientStock
{
    public function __invoke(Ingredient $ingredient, float $quantity, StockAdjustmentType $type, ?string $notes = null): void
    {
        $resulting = (float) $ingredient->current_stock + $quantity;

        throw_if($resulting < 0, StockWouldGoNegativeException::class, $ingredient, $quantity, $resulting);

        $ingredient->increment('current_stock', $quantity);

        $ingredient->stockAdjustments()->create([
            'quantity' => $quantity,
            'type' => $type,
            'notes' => $notes,
        ]);
    }
}
