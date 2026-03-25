<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerProfile> */
class CustomerProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'birthday' => fake()->optional()->date(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
