<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\ExpenseCategory;
use App\Models\Financial\Expense;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Expense> */
#[UseModel(Expense::class)]
class ExpenseFactory extends Factory
{
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

    /**
     * Expense in a specific category.
     */
    public function forCategory(ExpenseCategory $category): static
    {
        return $this->state(fn (array $attributes) => ['category' => $category]);
    }

    /**
     * Expense on a specific date.
     */
    public function forDate(Carbon|string $date): static
    {
        return $this->state(fn (array $attributes) => ['date' => $date]);
    }
}
