<?php

use App\Models\Financial\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('expense deductible amount is calculated on save', function () {
    $expense = Expense::factory()->create([
        'amount' => 100.00,
        'business_percentage' => 75,
    ]);

    expect($expense->deductible_amount->dollars())->toBe(75.00);
});

test('expense deductible amount recalculates on update', function () {
    $expense = Expense::factory()->create([
        'amount' => 200.00,
        'business_percentage' => 50,
    ]);

    $expense->update(['business_percentage' => 100]);

    expect($expense->fresh()->deductible_amount->dollars())->toBe(200.00);
});
