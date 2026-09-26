<?php

use App\DataTransferObjects\Financial\FinancialSummary;
use App\Enums\Financial\ExpenseCategory;
use App\Models\Customers\Customer;
use App\Models\Financial\Expense;
use App\Models\Financial\Income;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Financial\FinancialCalculator;
use App\ValueObjects\Money;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

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

it('aggregates monthly financial totals by date while preserving paid order and year scopes', function () {
    Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->delivered()
        ->count(2)
        ->sequence(
            ['total' => 125, 'delivery_date' => '2026-03-10'],
            ['total' => 75, 'delivery_date' => '2026-03-25'],
        )
        ->create();

    Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->delivered()
        ->create(['total' => 60, 'delivery_date' => '2026-04-01']);

    Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->unpaid()
        ->create(['total' => 500, 'delivery_date' => '2026-03-12']);

    Income::factory()->create(['amount' => 10, 'date' => '2026-01-20']);
    Income::factory()->create(['amount' => 40, 'date' => '2026-03-20']);
    Income::factory()->create(['amount' => 900, 'date' => '2025-03-20']);

    Expense::factory()->create([
        'description' => 'March ingredients',
        'amount' => 30,
        'category' => ExpenseCategory::Ingredients,
        'date' => '2026-03-08',
        'deductible_amount' => 30,
    ]);
    Expense::factory()->create([
        'description' => 'April supplies',
        'amount' => 15,
        'category' => ExpenseCategory::Supplies,
        'date' => '2026-04-18',
        'deductible_amount' => 15,
    ]);

    $groupedQueries = [];
    DB::listen(function (QueryExecuted $query) use (&$groupedQueries): void {
        if (str_contains(strtolower($query->sql), ' group by ')) {
            $groupedQueries[] = $query->sql;
        }
    });

    $result = (new FinancialCalculator)->calculate(2026);

    expect($result->monthlyBreakdown[0]->revenue)->toEqual(Money::fromDollars(10))
        ->and($result->monthlyBreakdown[2]->revenue)->toEqual(Money::fromDollars(240))
        ->and($result->monthlyBreakdown[2]->expenses)->toEqual(Money::fromDollars(30))
        ->and($result->monthlyBreakdown[2]->net)->toEqual(Money::fromDollars(210))
        ->and($result->monthlyBreakdown[3]->revenue)->toEqual(Money::fromDollars(60))
        ->and($result->monthlyBreakdown[3]->expenses)->toEqual(Money::fromDollars(15))
        ->and($result->monthlyBreakdown[3]->net)->toEqual(Money::fromDollars(45))
        ->and($groupedQueries)->toHaveCount(4);
});
