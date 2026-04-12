<?php

namespace Database\Factories\Customers;

use App\Models\Customers\CustomerFavorite;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerFavorite> */
#[UseModel(CustomerFavorite::class)]
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
