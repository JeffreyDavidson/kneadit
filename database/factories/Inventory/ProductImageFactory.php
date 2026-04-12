<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductImage;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
#[UseModel(ProductImage::class)]
class ProductImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'path' => fake()->filePath(),
            'sort_order' => fake()->numberBetween(0, 10),
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
