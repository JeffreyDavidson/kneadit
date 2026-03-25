<?php

namespace Database\Factories;

use App\Models\BlockedDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedDate> */
class BlockedDateFactory extends Factory
{
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
}
