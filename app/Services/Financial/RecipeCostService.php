<?php

namespace App\Services\Financial;

use App\Models\Recipe;
use Illuminate\Support\Collection;

class RecipeCostService
{
    /**
     * Calculate total recipe cost from ingredients.
     */
    public function calculateCosts(?Recipe $recipe): float
    {
        if (! $recipe || ! $recipe->ingredients) {
            return 0.0;
        }

        $totalCost = 0.0;

        foreach ($recipe->ingredients as $ingredient) {
            $cost = $ingredient['cost'] ?? 0;
            $quantity = $ingredient['quantity'] ?? 0;
            $totalCost += ($cost * $quantity);
        }

        return $totalCost;
    }

    /**
     * Calculate current margin percentage based on product price and recipe cost.
     */
    public function calculateCurrentMargin(?Recipe $recipe, float $totalRecipeCost): float
    {
        if (! $recipe || ! $recipe->product || ! $recipe->product->price || $totalRecipeCost <= 0) {
            return 0.0;
        }

        $productPrice = $recipe->product->price;

        return (($productPrice - $totalRecipeCost) / $productPrice) * 100;
    }

    /**
     * Calculate suggested price from cost and target margin.
     */
    public function calculateSuggestedPrice(float $totalRecipeCost, float $targetMarginPercentage): float
    {
        if ($totalRecipeCost <= 0 || $targetMarginPercentage <= 0) {
            return 0.0;
        }

        return $totalRecipeCost / (1 - ($targetMarginPercentage / 100));
    }

    /**
     * Format ingredient list with individual costs.
     *
     * @return Collection<int, mixed>
     */
    public function getFormattedIngredients(?Recipe $recipe): Collection
    {
        if (! $recipe || ! $recipe->ingredients) {
            return collect();
        }

        return collect($recipe->ingredients)->map(function (array $ingredient) {
            $cost = $ingredient['cost'] ?? 0;
            $quantity = $ingredient['quantity'] ?? 0;
            $totalCost = $cost * $quantity;

            return [
                'name' => $ingredient['name'] ?? '',
                'quantity' => $quantity,
                'unit' => $ingredient['unit'] ?? '',
                'cost_per_unit' => $cost,
                'total_cost' => $totalCost,
            ];
        });
    }
}
