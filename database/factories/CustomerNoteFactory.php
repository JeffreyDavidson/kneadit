<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerNote> */
class CustomerNoteFactory extends Factory
{
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
