<?php

namespace App\Builders\Inventory;

use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @extends Builder<Category> */
class CategoryQueryBuilder extends Builder
{
    public function active(): static
    {
        $this->where('is_active', true);

        return $this;
    }

    public function withActiveProducts(): static
    {
        $this->with(['products' => function (HasMany $q): void {
            $q->where('is_active', true)
                ->with(['primaryImage', 'seasonalItems'])
                ->orderBy('name');
        }]);

        return $this;
    }

    public function withFeaturedProducts(): static
    {
        $this->with(['products' => function (HasMany $q): void {
            $q->where('is_active', true)
                ->where('is_featured', true)
                ->with(['primaryImage', 'seasonalItems']);
        }]);

        return $this;
    }
}
