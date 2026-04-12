<?php

namespace Database\Factories\Engagement;

use App\Models\Engagement\Review;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
#[UseModel(Review::class)]
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'product_id' => Product::factory(),
            'order_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->paragraph(),
            'is_approved' => false,
            'is_featured' => false,
        ];
    }

    /**
     * Review is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['is_approved' => false]);
    }

    /**
     * Review has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['is_approved' => true]);
    }

    /**
     * Review is approved and featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'is_featured' => true,
        ]);
    }
}
