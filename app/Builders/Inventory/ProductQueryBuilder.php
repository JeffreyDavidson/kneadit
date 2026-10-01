<?php

namespace App\Builders\Inventory;

use App\Models\Inventory\Product;
use Carbon\CarbonInterface;
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

    /**
     * Products with no seasonal windows, or with at least one window (inclusive)
     * that covers the given date. Dates are compared as dates, never instants.
     */
    public function availableOn(CarbonInterface $date): static
    {
        return $this->where(function (Builder $query) use ($date): void {
            $query->whereDoesntHave('seasonalItems')
                ->orWhereHas('seasonalItems', fn (Builder $sq) => $sq
                    ->whereDate('available_from', '<=', $date)
                    ->whereDate('available_until', '>=', $date));
        });
    }
}
