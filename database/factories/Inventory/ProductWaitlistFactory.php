<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Product;
use App\Models\Inventory\ProductWaitlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductWaitlist> */
class ProductWaitlistFactory extends Factory
{
    protected $model = ProductWaitlist::class;

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
