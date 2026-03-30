<?php

namespace Database\Factories;

use App\Models\BusinessSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessSchedule>
 */
class BusinessScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_week' => fake()->numberBetween(0, 6),
            'is_open' => true,
            'open_time' => '08:00',
            'close_time' => '17:00',
        ];
    }

    /**
     * Business is closed on this day.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => ['is_open' => false]);
    }
}
