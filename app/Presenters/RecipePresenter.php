<?php

namespace App\Presenters;

use App\Models\Inventory\Recipe;
use App\Support\ProfitMargin;

final class RecipePresenter
{
    public function __construct(
        public readonly Recipe $recipe,
    ) {}

    public static function for(Recipe $recipe): self
    {
        $recipe->loadMissing('product');

        return new self($recipe);
    }

    public function profitMargin(): ?float
    {
        $this->recipe->loadMissing('product');

        if (! $this->recipe->product || ! $this->recipe->cost) {
            return null;
        }

        return ProfitMargin::calculate(
            (float) $this->recipe->product->price,
            (float) $this->recipe->cost,
            1,
        );
    }
}
