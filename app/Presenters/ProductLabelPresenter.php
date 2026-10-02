<?php

namespace App\Presenters;

use App\Enums\Inventory\Allergen;
use App\Enums\Inventory\MeasurementUnit;
use App\Enums\Inventory\UnitDimension;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use Illuminate\Support\Collection;

/**
 * Formats a Product for a printable compliance label — ingredients ordered
 * by weight descending (per FDA rules; see ingredientNames()) and a "Contains: …" allergen statement
 * derived from the union of its recipe ingredients' allergen tags.
 */
final readonly class ProductLabelPresenter
{
    public function __construct(public Product $product) {}

    public static function for(Product $product): self
    {
        $product->loadMissing(['recipe.inventoryIngredients']);

        return new self($product);
    }

    /**
     * Ingredient names ordered by weight, heaviest first — the FDA expects ingredients
     * listed by weight from most to least. Each linked recipe line is converted to grams
     * (equal weights keep the order they were entered in). A line in a volume, count or
     * unknown unit can't be weighed without a density, so it is listed after every weighed
     * line, in the order entered.
     *
     * @return list<string>
     */
    public function ingredientNames(): array
    {
        $recipe = $this->product->recipe;

        if (! $recipe) {
            return [];
        }

        $weighed = [];
        $unweighed = [];

        foreach ($recipe->inventoryIngredients as $ingredient) {
            $grams = $this->grams($ingredient);

            if ($grams === null) {
                $unweighed[] = $ingredient->name;

                continue;
            }

            $weighed[] = ['name' => $ingredient->name, 'grams' => $grams];
        }

        usort($weighed, fn (array $a, array $b): int => $b['grams'] <=> $a['grams']);

        $names = [...array_column($weighed, 'name'), ...$unweighed];

        if ($names !== []) {
            return $names;
        }

        return $this->fallbackIngredientNames($recipe);
    }

    /**
     * True when a linked recipe line can't be weighed (volume, count or unknown unit), so its
     * position on the label is approximate. Shown to the baker on screen, never on the printed label.
     */
    public function hasUnweighedIngredients(): bool
    {
        return $this->product->recipe?->inventoryIngredients
            ->contains(fn (Ingredient $ingredient): bool => $this->grams($ingredient) === null) ?? false;
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

        /** @var array<string, Allergen> $allergensByValue */
        $allergensByValue = [];

        foreach ($recipe->inventoryIngredients as $ingredient) {
            foreach ($ingredient->allergens ?? [] as $allergen) {
                $allergensByValue[$allergen->value] = $allergen;
            }
        }

        $allergens = array_values($allergensByValue);
        usort($allergens, fn (Allergen $a, Allergen $b): int => $a->getLabel() <=> $b->getLabel());

        return $allergens;
    }

    public function allergenStatement(): ?string
    {
        $allergens = $this->allergens();

        if ($allergens === []) {
            return null;
        }

        $labels = Collection::make($allergens)->map(fn (Allergen $a): string => $a->getLabel())->all();

        return 'Contains: '.implode(', ', $labels).'.';
    }

    /**
     * The line's weight in grams, or null when its unit isn't a known mass unit.
     */
    private function grams(Ingredient $ingredient): ?float
    {
        /** @var object{quantity: string, unit: string} $pivot */
        $pivot = $ingredient->pivot;
        $unit = MeasurementUnit::tryFrom($pivot->unit);

        if (! $unit instanceof MeasurementUnit || $unit->dimension() !== UnitDimension::Mass) {
            return null;
        }

        return $unit->convert((float) $pivot->quantity, MeasurementUnit::Grams);
    }

    /** @return list<string> */
    private function fallbackIngredientNames(Recipe $recipe): array
    {
        /** @var list<string> $rows */
        $rows = Collection::make($recipe->ingredients ?? [])
            ->reject(fn (array $row): bool => empty($row['name']))
            ->sortByDesc(fn (array $row): float => (float) ($row['quantity'] ?? 0))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return $rows;
    }
}
