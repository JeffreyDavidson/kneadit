<?php

namespace Database\Factories\Inventory;

use App\Enums\Inventory\MeasurementUnit;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Recipe;
use App\Models\Inventory\RecipeIngredient;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecipeIngredient>
 */
#[UseModel(RecipeIngredient::class)]
class RecipeIngredientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'ingredient_id' => Ingredient::factory(),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit' => MeasurementUnit::Kilograms->value,
        ];
    }
}
