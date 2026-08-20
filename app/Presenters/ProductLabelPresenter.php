<?php

namespace App\Presenters;

use App\Enums\Inventory\Allergen;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

/**
 * Formats a Product for a printable compliance label — ingredients ordered
 * by weight descending (per FDA rules) and a "Contains: …" allergen statement
 * derived from the union of its recipe ingredients' allergen tags.
 */
final class ProductLabelPresenter
{
    public function __construct(public readonly Product $product) {}

    public static function for(Product $product): self
    {
        $product->loadMissing(['recipe.inventoryIngredients']);

        return new self($product);
    }

    /**
     * Ingredient names ordered by quantity descending — the FDA expects
     * ingredients listed by weight from most to least.
     *
     * @return list<string>
     */
    public function ingredientNames(): array
    {
        $recipe = $this->product->recipe;

        if (! $recipe) {
            return [];
        }

        /** @var list<string> $relational */
        $relational = $recipe->inventoryIngredients
            ->sortByDesc(function (Ingredient $ingredient): float {
                $quantity = $ingredient->pivot->quantity ?? 0;

                return Number::parseFloat((string) $quantity) ?: 0.0;
            })
            ->pluck('name')
            ->values()
            ->all();

        if (! empty($relational)) {
            return $relational;
        }

        return $this->fallbackIngredientNames($recipe);
    }

    /**
     * Union of allergens across the recipe's linked ingredients.
     * Only works when the baker has tagged their pantry ingredients — recipes
     * that use only the free-text `ingredients` JSON return an empty set.
     *
     * @return list<Allergen>
     */
    public function allergens(): array
    {
        $recipe = $this->product->recipe;

        if (! $recipe) {
            return [];
        }

        /** @var list<Allergen> $allergens */
        $allergens = $recipe->inventoryIngredients
            ->flatMap(fn (Ingredient $i) => $i->allergens ?? collect())
            ->unique(fn (Allergen $a) => $a->value)
            ->sortBy(fn (Allergen $a) => $a->getLabel())
            ->values()
            ->all();

        return $allergens;
    }

    public function allergenStatement(): ?string
    {
        $allergens = $this->allergens();

        if (empty($allergens)) {
            return null;
        }

        $labels = Collection::make($allergens)->map(fn (Allergen $a) => $a->getLabel())->all();

        return 'Contains: ' . implode(', ', $labels) . '.';
    }

    /** @return list<string> */
    private function fallbackIngredientNames(Recipe $recipe): array
    {
        /** @var list<string> $rows */
        $rows = Collection::make($recipe->ingredients ?? [])
            ->reject(fn (array $row): bool => empty($row['name']))
            ->sortByDesc(fn (array $row) => (float) ($row['quantity'] ?? 0))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return $rows;
    }
}
