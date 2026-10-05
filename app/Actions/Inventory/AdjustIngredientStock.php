<?php

namespace App\Actions\Inventory;

use App\Enums\Inventory\StockAdjustmentType;
use App\Exceptions\Inventory\StockWouldGoNegativeException;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;

class AdjustIngredientStock
{
    /**
     * Change an ingredient's stock and record why. Refuses to go below zero unless
     * $allowNegative is set (a bake that already started has used the flour whether
     * or not the count says there was enough).
     *
     * @return float The stock after the change.
     *
     * @throws StockWouldGoNegativeException
     */
    public function __invoke(
        Ingredient $ingredient,
        float $quantity,
        StockAdjustmentType $type,
        ?string $notes = null,
        ?Order $order = null,
        bool $allowNegative = false,
    ): float {
        return DB::transaction(function () use ($ingredient, $quantity, $type, $notes, $order, $allowNegative): float {
            $lockedIngredient = Ingredient::query()
                ->whereKey($ingredient->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $resulting = (float) $lockedIngredient->current_stock + $quantity;

            throw_if($resulting < 0 && ! $allowNegative, StockWouldGoNegativeException::class, $lockedIngredient, $quantity, $resulting);

            $lockedIngredient->increment('current_stock', $quantity);

            $lockedIngredient->stockAdjustments()->create([
                'order_id' => $order?->id,
                'quantity' => $quantity,
                'type' => $type,
                'notes' => $notes,
            ]);

            return $resulting;
        });
    }
}
