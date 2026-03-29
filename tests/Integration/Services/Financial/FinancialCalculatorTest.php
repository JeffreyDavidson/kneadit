<?php

use App\DataTransferObjects\FinancialSummary;
use App\Enums\ExpenseCategory;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use App\Services\Financial\FinancialCalculator;

beforeEach(function () {
    setUpTenantTest();
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    $this->customer = Customer::query()->create(['name' => 'Jane', 'email' => 'jane@test.com']);
});

it('calculates yearly totals from orders and expenses', function () {
    Order::query()->create([
        'customer_id' => $this->customer->id,
        'status' => OrderStatus::Delivered,
        'payment_status' => PaymentStatus::Paid,
        'subtotal' => 100,
        'total' => 100,
        'delivery_date' => '2026-03-15',
    ]);

    Expense::query()->create([
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
