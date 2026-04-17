<?php

namespace App\Services\Financial;

use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;

/**
 * Resolves the best-available unit cost for a product.
 *
 * Priority: Product.cost > first Recipe.cost > sum of Recipe.ingredients JSON.
 * Returns 0.0 when no cost data can be found.
 */
class ProductCostResolver
{
    public function resolve(Product $product): float
    {
        $product->loadMissing('recipes');

        if ($product->cost && $product->cost > 0) {
            return (float) $product->cost;
        }

        $recipe = $product->recipes->where('cost', '>', 0)->first();
        if ($recipe) {
            return (float) $recipe->cost;
        }

        $recipe = $product->recipes->first();
        if ($recipe) {
            return $this->calculateRecipeCost($recipe);
        }

        return 0.0;
    }

    public function calculateRecipeCost(Recipe $recipe): float
    {
        if (! $recipe->ingredients) {
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
}
