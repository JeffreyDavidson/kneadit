<?php

namespace Database\Factories\Operations;

use App\Models\Operations\BlockedDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedDate>
 */
class BlockedDateFactory extends Factory
{
    protected $model = BlockedDate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('+1 day', '+3 months'),
            'reason' => fake()->optional()->sentence(),
            'is_all_day' => true,
        ];
    }

    /**
     * Blocked date covers the entire day.
     */
    public function allDay(): static
    {
        return $this->state(fn (array $attributes) => ['is_all_day' => true]);
    }

    /**
     * Blocked date covers only part of the day.
     */
    public function partialDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_all_day' => false,
            'open_time' => '09:00',
            'close_time' => '17:00',
        ]);
    }
}
