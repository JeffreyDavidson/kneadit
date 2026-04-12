<?php

namespace Database\Factories\Inventory;

use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\StockAdjustment;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment> */
#[UseModel(StockAdjustment::class)]
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
