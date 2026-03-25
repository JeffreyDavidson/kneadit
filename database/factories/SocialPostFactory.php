<?php

namespace Database\Factories;

use App\Models\SocialPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialPost> */
class SocialPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform' => fake()->randomElement(['instagram', 'facebook', 'twitter']),
            'caption' => fake()->paragraph(),
            'product_id' => null,
            'scheduled_at' => fake()->optional()->dateTimeBetween('+1 day', '+14 days'),
            'status' => 'draft',
        ];
    }
}
