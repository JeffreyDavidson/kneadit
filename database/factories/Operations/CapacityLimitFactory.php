<?php

namespace Database\Factories\Operations;

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
            'date' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'day_of_week' => fake()->numberBetween(0, 6),
            'max_orders' => fake()->numberBetween(5, 30),
        ];
    }
}
