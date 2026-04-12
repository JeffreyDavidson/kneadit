<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
#[UseModel(Recipe::class)]
class RecipeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->words(3, true),
            'ingredients' => [],
            'instructions' => fake()->paragraphs(3, true),
            'prep_time_minutes' => fake()->numberBetween(15, 120),
            'cost' => fake()->randomFloat(2, 2, 30),
        ];
    }
}
