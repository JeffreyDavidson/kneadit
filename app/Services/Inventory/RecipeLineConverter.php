<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Enums\Inventory\MeasurementUnit;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Recipe;

/**
 * Expresses a recipe's linked ingredient line in the ingredient's stock unit,
 * so a line of 500 g can be compared with, or deducted from, stock held in kg.
 */
final class RecipeLineConverter
{
    /**
     * The line's quantity per unit of product in the ingredient's stock unit, at full precision.
     * Null when the two units can't be converted (different dimensions, or a legacy unit that
     * isn't a known measurement unit); the caller skips such a line rather than block an order.
     */
    public function inStockUnit(Recipe $recipe, Ingredient $ingredient): ?float
    {
        /** @var object{quantity: string, unit: string} $pivot */
        $pivot = $ingredient->pivot;
        $quantity = (float) $pivot->quantity;

        if ($pivot->unit === $ingredient->unit) {
            return $quantity;
        }

        $recipeUnit = MeasurementUnit::tryFrom($pivot->unit);
        $stockUnit = $ingredient->measurement_unit;
        $converted = $recipeUnit instanceof MeasurementUnit && $stockUnit instanceof MeasurementUnit
            ? $recipeUnit->convert($quantity, $stockUnit)
            : null;

        if ($converted === null) {
            logger()->warning('Skipped a recipe ingredient line whose unit cannot be converted to the ingredient stock unit.', [
                'recipe' => $recipe->name,
                'ingredient' => $ingredient->name,
                'recipe_unit' => $pivot->unit,
                'stock_unit' => $ingredient->unit,
            ]);
        }

        return $converted;
    }
}
