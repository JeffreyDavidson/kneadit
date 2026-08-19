<?php

namespace App\Builders\Inventory;

use App\Models\Inventory\Ingredient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/** @extends Builder<Ingredient> */
class IngredientQueryBuilder extends Builder
{
    public function lowStock(): static
    {
        $this->where(function (Builder $query): void {
            $query->where('current_stock', '<=', 0)
                ->orWhereColumn('current_stock', '<=', 'low_stock_threshold');
        });

        return $this;
    }

    public function outOfStock(): static
    {
        $this->where('current_stock', '<=', 0);

        return $this;
    }

    public function withActiveSuppliers(): static
    {
        $this->with(['suppliers' => function (Relation $query): void {
            $query->where('is_active', true);
        }]);

        return $this;
    }
}
