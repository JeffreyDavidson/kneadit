<?php

namespace Database\Factories;

use App\Models\CustomerPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerPhoto> */
class CustomerPhotoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'caption' => fake()->optional()->sentence(),
            'photo_path' => 'customer-photos/'.fake()->uuid().'.jpg',
            'product_id' => null,
            'is_approved' => false,
            'is_featured' => false,
        ];
    }
}
