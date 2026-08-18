<?php

use App\Enums\Financial\ExpenseCategory;
use App\Models\Financial\Expense;

beforeEach(function () {
    setUpTenantTest();
});

test('expense category is cast to ExpenseCategory enum', function () {
    $expense = Expense::factory()->forCategory(ExpenseCategory::Ingredients)->create();

    expect($expense->fresh()->category)->toBe(ExpenseCategory::Ingredients);
});
