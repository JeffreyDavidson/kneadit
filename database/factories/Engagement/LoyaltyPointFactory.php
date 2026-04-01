<?php

namespace Database\Factories\Engagement;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyPoint>
 */
class LoyaltyPointFactory extends Factory
{
    protected $model = LoyaltyPoint::class;

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
