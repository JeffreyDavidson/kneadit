<?php

namespace App\Builders\Inventory;

use App\Models\Inventory\Ingredient;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Ingredient> */
class IngredientQueryBuilder extends Builder
{
    public function lowStock(): static
    {
        return $this->where(function (Builder $query): void {
            $query->where('current_stock', '<=', 0)
                ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
        });
    }
}
