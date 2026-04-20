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
            'reward_type' => RewardType::PercentageDiscount,
            'discount_percentage' => fake()->randomFloat(2, 5, 25),
            'discount_amount' => null,
            'product_id' => null,
            'is_active' => true,
        ];
    }

    /**
     * Loyalty reward is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => true]);
    }

    /**
     * Loyalty reward is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Percentage-discount reward.
     */
    public function percentageDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'reward_type' => RewardType::PercentageDiscount,
            'discount_percentage' => $attributes['discount_percentage'] ?? fake()->randomFloat(2, 5, 25),
            'discount_amount' => null,
            'product_id' => null,
        ]);
    }

    /**
     * Fixed-amount-discount reward.
     */
    public function fixedDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'reward_type' => RewardType::FixedDiscount,
            'discount_amount' => $attributes['discount_amount'] ?? fake()->randomFloat(2, 5, 25),
            'discount_percentage' => null,
            'product_id' => null,
        ]);
    }

    /**
     * Free-product reward.
     */
    public function freeProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'reward_type' => RewardType::FreeProduct,
            'discount_amount' => null,
            'discount_percentage' => null,
        ]);
    }
}
