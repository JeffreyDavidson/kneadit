<?php

namespace Database\Factories\Customers;

use App\Enums\Customers\CateringEventType;
use App\Enums\Customers\CateringInquiryStatus;
use App\Models\Customers\CateringInquiry;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CateringInquiry> */
#[UseModel(CateringInquiry::class)]
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
            'customer_phone' => fake()->numerify('###-###-####'),
            'event_type' => fake()->randomElement(CateringEventType::cases()),
            'event_date' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'guest_count' => fake()->numberBetween(10, 200),
            'budget' => fake()->optional()->randomFloat(2, 200, 5000),
            'details' => fake()->paragraph(),
            'status' => CateringInquiryStatus::Inquiry,
        ];
    }

    public function quoted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CateringInquiryStatus::Quoted,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CateringInquiryStatus::Confirmed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CateringInquiryStatus::Cancelled,
        ]);
    }
}
