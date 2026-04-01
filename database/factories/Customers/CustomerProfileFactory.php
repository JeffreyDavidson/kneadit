<?php

namespace Database\Factories\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerProfile> */
class CustomerProfileFactory extends Factory
{
    protected $model = CustomerProfile::class;

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
