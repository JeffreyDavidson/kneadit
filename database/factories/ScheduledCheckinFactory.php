<?php

namespace Database\Factories;

use App\Models\ScheduledCheckin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduledCheckin> */
class ScheduledCheckinFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'days_after_signup' => fake()->numberBetween(1, 30),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
            'is_active' => true,
        ];
    }
}
