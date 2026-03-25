<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductWaitlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductWaitlist> */
class ProductWaitlistFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'customer_email' => fake()->safeEmail(),
            'customer_name' => fake()->name(),
        ];
    }
}
