<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerReminder> */
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
