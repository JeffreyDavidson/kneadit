<?php

use App\Enums\Financial\ExpenseCategory;
use App\Models\Financial\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('forYear filters expenses by year', function () {
    Expense::factory()->forDate('2026-03-15')->create();
    Expense::factory()->forDate('2025-06-01')->create();

    $results = Expense::query()->forYear(2026)->get();

    expect($results)->toHaveCount(1);
});

test('forMonth filters expenses by year and month', function () {
    Expense::factory()->forDate('2026-03-15')->create();
    Expense::factory()->forDate('2026-04-01')->create();

    $results = Expense::query()->forMonth(2026, 3)->get();

    expect($results)->toHaveCount(1);
});

test('cogs returns only ingredients and packaging expenses', function () {
    Expense::factory()->forCategory(ExpenseCategory::Ingredients)->create();
    Expense::factory()->forCategory(ExpenseCategory::Packaging)->create();
    Expense::factory()->forCategory(ExpenseCategory::Marketing)->create();
    Expense::factory()->forCategory(ExpenseCategory::Equipment)->create();

    $results = Expense::query()->cogs()->get();

    expect($results)->toHaveCount(2)
        ->and($results->pluck('category')->all())->each->toBeIn([
            ExpenseCategory::Ingredients,
            ExpenseCategory::Packaging,
        ]);
});

test('byCategory groups expenses and sums amounts by category', function () {
    Expense::factory()->forCategory(ExpenseCategory::Ingredients)->create(['amount' => 50.00]);
    Expense::factory()->forCategory(ExpenseCategory::Ingredients)->create(['amount' => 30.00]);
    Expense::factory()->forCategory(ExpenseCategory::Marketing)->create(['amount' => 100.00]);

    $results = Expense::query()->byCategory()->get();

    expect($results)->toHaveCount(2);

    $ingredients = $results->firstWhere('category', ExpenseCategory::Ingredients);
    $marketing = $results->firstWhere('category', ExpenseCategory::Marketing);

    expect((float) $ingredients->total_amount)->toBe(80.00)
        ->and((float) $marketing->total_amount)->toBe(100.00);
});
