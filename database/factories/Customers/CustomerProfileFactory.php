<?php

namespace Database\Factories\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerProfile;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerProfile> */
#[UseModel(CustomerProfile::class)]
class CustomerProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
