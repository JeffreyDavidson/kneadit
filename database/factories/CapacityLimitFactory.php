<?php

namespace Database\Factories;

use App\Models\CapacityLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CapacityLimit> */
class CapacityLimitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_week' => fake()->numberBetween(0, 6),
            'max_orders' => fake()->numberBetween(5, 30),
        ];
    }
}
