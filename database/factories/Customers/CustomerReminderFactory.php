<?php

namespace Database\Factories\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReminder;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerReminder> */
#[UseModel(CustomerReminder::class)]
class CustomerReminderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'last_order_date' => fake()->dateTimeBetween('-60 days', '-1 day'),
            'reminder_sent_at' => null,
        ];
    }
}
