<?php

namespace Database\Factories\Engagement;

use App\Enums\Engagement\RewardType;
use App\Models\Engagement\LoyaltyReward;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyReward>
 */
#[UseModel(LoyaltyReward::class)]
class LoyaltyRewardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'points_required' => fake()->numberBetween(50, 500),
            'reward_type' => fake()->randomElement(RewardType::cases()),
            'reward_value' => fake()->randomFloat(2, 5, 25),
            'product_id' => null,
            'is_active' => true,
        ];
    }

    /**
     * Loyalty reward is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
