<?php

namespace Database\Factories\Operations;

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\CapacityLimit;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CapacityLimit> */
#[UseModel(CapacityLimit::class)]
class CapacityLimitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_week' => fake()->randomElement(array_column(DayOfWeek::cases(), 'value')),
            'max_orders' => fake()->numberBetween(5, 30),
        ];
    }

    /**
     * Limit recurs every week on the given weekday.
     */
    public function weekday(DayOfWeek $day): static
    {
        return $this->state(fn (array $attributes) => [
            'specific_date' => null,
            'day_of_week' => $day->value,
        ]);
    }

    /**
     * Limit applies to one specific date rather than a recurring weekday.
     */
    public function specificDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'specific_date' => $date,
            'day_of_week' => null,
        ]);
    }

    /**
     * Day is blocked (no orders accepted).
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => ['is_blocked' => true]);
    }

    /**
     * Day is open for orders.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => ['is_blocked' => false]);
    }
}
