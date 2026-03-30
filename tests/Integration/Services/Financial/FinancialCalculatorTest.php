<?php

use App\DataTransferObjects\FinancialSummary;
use App\Enums\ExpenseCategory;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use App\Services\Financial\FinancialCalculator;

beforeEach(function () {
    setUpTenantTest();
    $this->user = User::factory()->owner()->create();
    $this->customer = Customer::factory()->create();
});

it('calculates yearly totals from orders and expenses', function () {
    Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create([
            'subtotal' => 100,
            'total' => 100,
            'delivery_date' => '2026-03-15',
        ]);

    Expense::factory()->create([
        'description' => 'Flour',
        'amount' => 30,
        'category' => ExpenseCategory::Ingredients,
        'date' => '2026-03-15',
        'deductible_amount' => 30,
    ]);

    $calculator = new FinancialCalculator;
    $result = $calculator->calculate(2026);

    expect($result)->toBeInstanceOf(FinancialSummary::class)
        ->and($result->totalRevenue)->toBe(100.0)
        ->and($result->totalExpenses)->toBe(30.0)
        ->and($result->netProfit)->toBe(70.0);
});
