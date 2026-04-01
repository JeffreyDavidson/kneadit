<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\ExpenseCategory;
use App\Models\Financial\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense> */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'category' => fake()->randomElement(ExpenseCategory::cases()),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
