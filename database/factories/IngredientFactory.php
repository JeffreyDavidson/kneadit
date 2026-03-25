<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'unit' => fake()->randomElement(['kg', 'g', 'L', 'mL', 'cups', 'tbsp']),
            'current_stock' => fake()->randomFloat(2, 10, 100),
            'low_stock_threshold' => 5.00,
            'cost_per_unit' => fake()->randomFloat(2, 1, 20),
        ];
    }

    /**
     * Stock is below the low threshold.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => ['current_stock' => 3.00, 'low_stock_threshold' => 5.00]);
    }

    /**
     * No stock remaining.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => ['current_stock' => 0]);
    }
}
