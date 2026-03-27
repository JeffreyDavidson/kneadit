<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;
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
            'platform' => fake()->randomElement(SocialPlatform::cases()),
            'caption' => fake()->paragraph(),
            'product_id' => null,
            'scheduled_for' => fake()->optional()->dateTimeBetween('+1 day', '+14 days'),
            'status' => SocialPostStatus::Draft,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SocialPostStatus::Scheduled,
            'scheduled_for' => fake()->dateTimeBetween('+1 day', '+14 days'),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SocialPostStatus::Posted,
        ]);
    }
}
