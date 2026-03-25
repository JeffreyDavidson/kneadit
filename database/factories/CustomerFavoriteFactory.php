<?php

namespace Database\Factories;

use App\Models\CustomerFavorite;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerFavorite> */
class CustomerFavoriteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_email' => fake()->safeEmail(),
            'product_id' => Product::factory(),
        ];
    }
}
