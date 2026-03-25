<?php

namespace Database\Factories;

use App\Models\CateringInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CateringInquiry> */
class CateringInquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'event_type' => fake()->randomElement(['wedding', 'corporate', 'birthday', 'other']),
            'event_date' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'guest_count' => fake()->numberBetween(10, 200),
            'budget' => fake()->optional()->randomFloat(2, 200, 5000),
            'details' => fake()->paragraph(),
            'status' => 'new',
        ];
    }
}
