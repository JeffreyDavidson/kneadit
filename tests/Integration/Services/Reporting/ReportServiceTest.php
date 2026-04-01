<?php

use App\Enums\Financial\ExpenseCategory;
use App\Models\Customers\Customer;
use App\Models\Financial\Expense;
use App\Models\Inventory\Category;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Reporting\ReportService;

beforeEach(function () {
    setUpTenantTest();
    $this->user = User::factory()->owner()->create();
    $this->service = resolve(ReportService::class);
    $this->customer = Customer::factory()->create();
    $this->category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
});

function createPaidOrder(float $total, string $date): Order
{
    return Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->delivered()
        ->create([
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

    expect($report['totalOrders'])->toBe(2)->and((float) $report['totalRevenue'])->toBe(80.00);
});

test('sales report respects date range', function () {
    createPaidOrder(50.00, '2026-02-15');
    createPaidOrder(30.00, '2026-03-15');

    $report = $this->service->salesReport('2026-03-01', '2026-03-31');

    expect($report['totalOrders'])->toBe(1)->and((float) $report['totalRevenue'])->toBe(30.00);
});

test('customer report counts new customers', function () {
    $countBefore = Customer::query()->count();
    Customer::factory()->create();

    expect(Customer::query()->count())->toBe($countBefore + 1);
});

test('financial summary calculates profit', function () {
    createPaidOrder(100.00, '2026-03-15');
    Expense::factory()->create([
        'description' => 'Flour',
        'amount' => 30.00,
        'category' => ExpenseCategory::Supplies,
        'date' => '2026-03-15',
        'deductible_amount' => 30.00,
    ]);

    $report = $this->service->financialSummary(2026);

    expect((float) $report['totalRevenue'])->toBe(100.00)->and((float) $report['totalExpenses'])->toBe(30.00)->and((float) $report['profit'])->toBe(70.00);
});

test('inventory report flags low stock', function () {
    Ingredient::factory()->create([
        'name' => 'Flour',
        'unit' => 'kg',
        'current_stock' => 5,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 1.50,
    ]);
    Ingredient::factory()->create([
        'name' => 'Sugar',
        'unit' => 'kg',
        'current_stock' => 50,
        'low_stock_threshold' => 10,
        'cost_per_unit' => 2.00,
    ]);

    $report = $this->service->inventoryReport();

    expect($report['lowStockItems'])->toBe(1);
    $flour = collect($report['ingredients'])->firstWhere('name', 'Flour');
    expect($flour['is_low'])->toBeTrue();
});
