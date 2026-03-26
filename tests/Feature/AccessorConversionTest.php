<?php

use App\Enums\ExpenseCategory;
use App\Models\Expense;

beforeEach(function () {
    setUpTenantTest();
});

test('expense category_label accessor returns category label', function () {
    $expense = Expense::factory()->create(['category' => ExpenseCategory::Ingredients]);

    expect($expense->category_label)->toBe('Ingredients');
});
