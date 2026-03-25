<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('+1 month', '+6 months');

        return [
            'name' => fake()->words(2, true),
            'date' => $date,
            'order_deadline' => Carbon::parse($date)->subDays(3),
            'prep_start' => Carbon::parse($date)->subDays(5),
            'max_orders' => fake()->optional()->numberBetween(10, 50),
            'is_active' => true,
            'notes' => null,
        ];
    }

    /**
     * Holiday is in the future.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->addDays(14),
            'order_deadline' => now()->addDays(11),
        ]);
    }

    /**
     * Holiday has already passed.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->subDays(14),
            'order_deadline' => now()->subDays(17),
        ]);
    }
}
