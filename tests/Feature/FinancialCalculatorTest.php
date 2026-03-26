<?php

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\User;
use App\Services\Financial\FinancialCalculator;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    $this->customer = Customer::query()->create(['name' => 'Jane', 'email' => 'jane@test.com']);
});

it('calculates yearly totals from orders and expenses', function () {
    Order::query()->create([
        'customer_id' => $this->customer->id,
        'status' => 'delivered',
        'payment_status' => 'paid',
        'subtotal' => 100,
        'total' => 100,
        'delivery_date' => '2026-03-15',
    ]);

    Expense::query()->create([
        'description' => 'Flour',
        'amount' => 30,
        'category' => 'ingredients',
        'date' => '2026-03-15',
        'deductible_amount' => 30,
    ]);

    $calculator = new FinancialCalculator;
    $result = $calculator->calculate(2026);

    expect($result)->toHaveKeys([
        'totalRevenue', 'totalExpenses', 'netProfit',
        'monthlyBreakdown', 'expenseBreakdown',
        'cogsAmount', 'cogsPercentage',
    ]);
    expect((float) $result['totalRevenue'])->toBe(100.0);
    expect((float) $result['totalExpenses'])->toBe(30.0);
    expect((float) $result['netProfit'])->toBe(70.0);
});
