<?php

namespace Database\Factories;

use App\Enums\StockAdjustmentType;
use App\Models\Ingredient;
use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment> */
class StockAdjustmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ingredient_id' => Ingredient::factory(),
            'quantity' => fake()->randomFloat(2, -10, 10),
            'type' => fake()->randomElement(StockAdjustmentType::cases()),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
