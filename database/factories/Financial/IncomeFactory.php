<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\IncomeSource;
use App\Models\Financial\Income;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Income> */
class IncomeFactory extends Factory
{
    protected $model = Income::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 20, 1000),
            'source' => fake()->randomElement(IncomeSource::cases()),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
