<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Ingredient;
use App\Models\Inventory\IngredientSupplier;
use App\Models\Inventory\Supplier;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IngredientSupplier>
 */
#[UseModel(IngredientSupplier::class)]
class IngredientSupplierFactory extends Factory
{
    /**
     * Prices are given in dollars; the pivot's money cast stores cents.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ingredient_id' => Ingredient::factory(),
            'supplier_id' => Supplier::factory(),
            'unit_price' => fake()->randomFloat(2, 0.5, 20),
            'minimum_order' => fake()->randomFloat(2, 10, 200),
            'lead_time_days' => fake()->numberBetween(1, 7),
            'sku' => fake()->bothify('SKU-####'),
        ];
    }
}
