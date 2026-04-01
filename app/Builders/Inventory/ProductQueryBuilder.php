<?php

namespace App\Builders\Inventory;

use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Product> */
class ProductQueryBuilder extends Builder
{
    public function active(): static
    {
        return $this->where('is_active', true);
    }

    public function featured(): static
    {
        return $this->where('is_featured', true);
    }

    public function inSeason(): static
    {
        return $this->where(function (Builder $query) {
            $query->whereDoesntHave('seasonalItems')
                ->orWhereHas('seasonalItems', fn (Builder $sq) => $sq
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now()));
        });
    }
}
