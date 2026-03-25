<?php

namespace Database\Factories;

use App\Models\LoyaltyReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyReward>
 */
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
            'reward_type' => 'percentage_discount',
            'reward_value' => fake()->randomFloat(2, 5, 25),
            'product_id' => null,
            'is_active' => true,
        ];
    }
}
