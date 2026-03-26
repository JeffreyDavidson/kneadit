<?php

use App\Enums\ExpenseCategory;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\User;
use App\Services\Reporting\ReportService;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    $this->user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    $this->service = resolve(ReportService::class);
    $this->customer = Customer::query()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $this->category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread']);
});

function createPaidOrder(float $total, string $date): Order
{
    return Order::query()->create([
        'customer_id' => test()->customer->id,
        'user_id' => test()->user->id,
        'status' => OrderStatus::Delivered,
        'payment_status' => PaymentStatus::Paid,
        'subtotal' => $total,
        'total' => $total,
        'delivery_date' => $date,
    ]);
}

test('service exists', function () {
    expect($this->service)->toBeInstanceOf(ReportService::class);
});

test('sales report returns correct totals', function () {
    createPaidOrder(50.00, '2026-03-01');
    createPaidOrder(30.00, '2026-03-02');

    $report = $this->service->salesReport('2026-03-01', '2026-03-31');

    expect($report['totalOrders'])->toBe(2);
    expect((float) $report['totalRevenue'])->toBe(80.00);
});

test('sales report respects date range', function () {
    createPaidOrder(50.00, '2026-02-15');
    createPaidOrder(30.00, '2026-03-15');

    $report = $this->service->salesReport('2026-03-01', '2026-03-31');

    expect($report['totalOrders'])->toBe(1);
    expect((float) $report['totalRevenue'])->toBe(30.00);
});

test('customer report counts new customers', function () {
    $countBefore = Customer::query()->count();
    Customer::query()->create(['name' => 'New Guy', 'email' => 'new@example.com']);

    expect(Customer::query()->count())->toBe($countBefore + 1);
});

test('financial summary calculates profit', function () {
    createPaidOrder(100.00, '2026-03-15');
    Expense::query()->create(['description' => 'Flour', 'amount' => 30.00, 'category' => ExpenseCategory::Supplies, 'date' => '2026-03-15', 'deductible_amount' => 30.00]);

    $report = $this->service->financialSummary(2026);

    expect((float) $report['totalRevenue'])->toBe(100.00);
    expect((float) $report['totalExpenses'])->toBe(30.00);
    expect((float) $report['profit'])->toBe(70.00);
});

test('inventory report flags low stock', function () {
    Ingredient::query()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 5, 'low_stock_threshold' => 10, 'cost_per_unit' => 1.50]);
    Ingredient::query()->create(['name' => 'Sugar', 'unit' => 'kg', 'current_stock' => 50, 'low_stock_threshold' => 10, 'cost_per_unit' => 2.00]);

    $report = $this->service->inventoryReport();

    expect($report['lowStockItems'])->toBe(1);
    $flour = collect($report['ingredients'])->firstWhere('name', 'Flour');
    expect($flour['is_low'])->toBeTrue();
});
