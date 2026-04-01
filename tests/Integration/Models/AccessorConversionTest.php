<?php

use App\Enums\Financial\ExpenseCategory;
use App\Models\Financial\Expense;

beforeEach(function () {
    setUpTenantTest();
});

test('expense category_label accessor returns category label', function () {
    $expense = Expense::factory()->create(['category' => ExpenseCategory::Ingredients]);

    expect($expense->category_label)->toBe('Ingredients');
});
