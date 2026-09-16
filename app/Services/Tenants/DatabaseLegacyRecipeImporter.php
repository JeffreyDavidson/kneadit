<?php

namespace App\Services\Tenants;

use App\Services\Tenants\Contracts\LegacyRecipeImporter;
use Illuminate\Support\Facades\DB;

class DatabaseLegacyRecipeImporter implements LegacyRecipeImporter
{
    public function __construct(private readonly LegacyImportValueParser $parser) {}

    /** @param array<int, array<string, mixed>> $recipes @param array<int, array<string, mixed>> $ingredients @param array<int, array<string, mixed>> $stages @param array<int, int> $productIds */
    public function import(array $recipes, array $ingredients, array $stages, array $productIds): void
    {
        foreach ($recipes as $recipe) {
            $recipeIngredients = array_values(array_map(
                fn (array $ingredient): array => ['name' => $ingredient['name'], 'quantity' => $this->parser->number($ingredient['quantity']), 'unit' => $ingredient['unit'], 'cost' => $this->parser->number($ingredient['cost_per_unit'] ?? 0)],
                array_filter($ingredients, fn (array $ingredient): bool => $this->parser->integer($ingredient['recipe_id']) === $this->parser->integer($recipe['id'])),
            ));
            $recipeStages = array_values(array_filter($stages, fn (array $stage): bool => $this->parser->integer($stage['recipe_id']) === $this->parser->integer($recipe['id'])));
            usort($recipeStages, fn (array $first, array $second): int => ($first['sort_order'] ?? 0) <=> ($second['sort_order'] ?? 0));
            $instructions = collect($recipeStages)->map(fn (array $stage): string => $this->parser->string($stage['name'])."\n".$this->parser->string($stage['instructions']))->implode("\n\n");
            $cost = collect($recipeIngredients)->sum(fn (array $ingredient): float => $ingredient['quantity'] * $ingredient['cost']);

            DB::table('recipes')->updateOrInsert(
                ['name' => $recipe['name']],
                ['product_id' => isset($recipe['product_id']) ? ($productIds[$this->parser->integer($recipe['product_id'])] ?? null) : null, 'ingredients' => json_encode($recipeIngredients, JSON_THROW_ON_ERROR), 'instructions' => $instructions ?: ($recipe['description'] ?? ''), 'prep_time_minutes' => $recipe['prep_time_minutes'] ?? 0, 'cost' => $this->parser->cents($cost), 'created_at' => $recipe['created_at'] ?? now(), 'updated_at' => $recipe['updated_at'] ?? now()],
            );
        }
    }
}
