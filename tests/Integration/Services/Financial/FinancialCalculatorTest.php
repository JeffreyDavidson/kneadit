<?php

use App\DataTransferObjects\Financial\FinancialSummary;
use App\Enums\Financial\ExpenseCategory;
use App\Models\Customers\Customer;
use App\Models\Financial\Expense;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Financial\FinancialCalculator;
use App\ValueObjects\Money;

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
});

it('calculates yearly totals from orders and expenses', function () {
    Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
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
        ->and($result->totalRevenue)->toEqual(Money::fromDollars(100))
        ->and($result->totalExpenses)->toEqual(Money::fromDollars(30))
        ->and($result->netProfit)->toEqual(Money::fromDollars(70))
        ->and($result->monthlyBreakdown[2]->revenue)->toEqual(Money::fromDollars(100))
        ->and($result->monthlyBreakdown[2]->expenses)->toEqual(Money::fromDollars(30))
        ->and($result->monthlyBreakdown[2]->net)->toEqual(Money::fromDollars(70))
        ->and($result->cogsAmount)->toEqual(Money::fromDollars(30))
        ->and($result->cogsPercentage)->toBe(100.0)
        ->and($result->expenseBreakdown->first()->amount)->toEqual(Money::fromDollars(30));
});
