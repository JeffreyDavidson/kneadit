<?php

namespace App\Actions\Inventory;

use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Inventory\Ingredient;

class AdjustIngredientStock
{
    public function __invoke(Ingredient $ingredient, float $quantity, StockAdjustmentType $type, ?string $notes = null): void
    {
        $ingredient->increment('current_stock', $quantity);

        $ingredient->stockAdjustments()->create([
            'quantity' => $quantity,
            'type' => $type,
            'notes' => $notes,
        ]);
    }
}
