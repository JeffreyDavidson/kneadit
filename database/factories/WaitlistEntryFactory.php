<?php

namespace Database\Factories;

use App\Enums\WaitlistStatus;
use App\Models\Product;
use App\Models\WaitlistEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaitlistEntry>
 */
class WaitlistEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->optional()->phoneNumber(),
            'product_id' => Product::factory(),
            'requested_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'status' => WaitlistStatus::Waiting,
            'notes' => null,
        ];
    }

    /**
     * Customer has been notified.
     */
    public function notified(): static
    {
        return $this->state(fn (array $attributes) => ['status' => WaitlistStatus::Notified]);
    }

    /**
     * Customer converted to an order.
     */
    public function converted(): static
    {
        return $this->state(fn (array $attributes) => ['status' => WaitlistStatus::Converted]);
    }
}
