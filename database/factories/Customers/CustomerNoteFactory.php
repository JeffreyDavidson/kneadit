<?php

namespace Database\Factories\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerNote;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerNote> */
class CustomerNoteFactory extends Factory
{
    protected $model = CustomerNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'note' => fake()->paragraph(),
            'created_by' => User::factory(),
        ];
    }
}
