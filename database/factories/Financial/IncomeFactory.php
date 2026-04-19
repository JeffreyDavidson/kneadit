<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\IncomeSource;
use App\Models\Financial\Income;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Income> */
#[UseModel(Income::class)]
class IncomeFactory extends Factory
{
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

    /**
     * Income from a specific source.
     */
    public function forSource(IncomeSource $source): static
    {
        return $this->state(fn (array $attributes) => ['source' => $source]);
    }

    /**
     * Income on a specific date.
     */
    public function forDate(Carbon|string $date): static
    {
        return $this->state(fn (array $attributes) => ['date' => $date]);
    }
}
