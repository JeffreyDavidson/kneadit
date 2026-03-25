<?php

namespace Database\Factories;

use App\Enums\LoyaltyPointType;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyPoint>
 */
class LoyaltyPointFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'points' => fake()->numberBetween(10, 500),
            'type' => LoyaltyPointType::Earned,
            'description' => fake()->sentence(),
            'order_id' => null,
        ];
    }

    /**
     * Points were earned from an order.
     */
    public function earned(int $points = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LoyaltyPointType::Earned,
            'points' => $points,
        ]);
    }

    /**
     * Points were redeemed for a reward.
     */
    public function redeemed(int $points = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LoyaltyPointType::Redeemed,
            'points' => $points,
        ]);
    }

    /**
     * Points were manually adjusted.
     */
    public function adjusted(int $points = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => LoyaltyPointType::Adjusted,
            'points' => $points,
        ]);
    }
}
