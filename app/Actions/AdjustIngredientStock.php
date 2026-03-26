<?php

namespace App\Actions;

use App\Models\Ingredient;

class AdjustIngredientStock
{
    public function __invoke(Ingredient $ingredient, float $quantity, string $type, ?string $notes = null): void
    {
        $ingredient->increment('current_stock', $quantity);

        $ingredient->stockAdjustments()->create([
            'quantity' => $quantity,
            'type' => $type,
            'notes' => $notes,
        ]);
    }
}
