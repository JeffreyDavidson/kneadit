<?php

namespace Database\Factories\Customers;

use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaitlistEntry>
 */
#[UseModel(WaitlistEntry::class)]
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
            'customer_phone' => fake()->optional()->passthrough(fake()->numerify('###-###-####')),
            'product_id' => Product::factory(),
            'requested_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'status' => WaitlistStatus::Waiting,
            'notes' => null,
        ];
    }

    /**
     * Customer is still waiting.
     */
    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => ['status' => WaitlistStatus::Waiting]);
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
