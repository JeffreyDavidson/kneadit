<?php

namespace Database\Factories\Customers;

use App\Models\Customers\CustomerPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerPhoto>
 */
class CustomerPhotoFactory extends Factory
{
    protected $model = CustomerPhoto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'caption' => fake()->optional()->sentence(),
            'photo_path' => 'customer-photos/' . fake()->uuid() . '.jpg',
            'product_id' => null,
            'is_approved' => false,
            'is_featured' => false,
        ];
    }

    /**
     * Photo has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['is_approved' => true]);
    }

    /**
     * Photo is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => ['is_featured' => true]);
    }
}
