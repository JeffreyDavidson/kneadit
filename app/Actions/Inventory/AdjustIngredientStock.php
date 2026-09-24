<?php

namespace App\Actions\Inventory;

use App\Enums\Inventory\StockAdjustmentType;
use App\Exceptions\Inventory\StockWouldGoNegativeException;
use App\Models\Inventory\Ingredient;
use Illuminate\Support\Facades\DB;

class AdjustIngredientStock
{
    public function __invoke(Ingredient $ingredient, float $quantity, StockAdjustmentType $type, ?string $notes = null): void
    {
        DB::transaction(function () use ($ingredient, $quantity, $type, $notes): void {
            $lockedIngredient = Ingredient::query()
                ->whereKey($ingredient->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $resulting = (float) $lockedIngredient->current_stock + $quantity;

            throw_if($resulting < 0, StockWouldGoNegativeException::class, $lockedIngredient, $quantity, $resulting);

            $lockedIngredient->increment('current_stock', $quantity);

            $lockedIngredient->stockAdjustments()->create([
                'quantity' => $quantity,
                'type' => $type,
                'notes' => $notes,
            ]);
        });
    }
}
