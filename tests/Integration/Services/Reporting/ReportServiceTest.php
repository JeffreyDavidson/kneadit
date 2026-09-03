<?php

use App\DataTransferObjects\Inventory\ProductReportResult;
use App\Enums\Financial\ExpenseCategory;
use App\Models\Customers\Customer;
use App\Models\Financial\Expense;
use App\Models\Inventory\Category;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Reports\Customers\CustomerReport;
use App\Reports\Financial\FinancialReport;
use App\Reports\Inventory\InventoryReport;
use App\Reports\Inventory\ProductReport;
use App\Reports\Orders\SalesReport;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
    test()->category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
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

test('sales report returns correct totals', function () {
    createPaidOrder(50.00, '2026-03-01');
    createPaidOrder(30.00, '2026-03-02');

    $report = resolve(SalesReport::class)->generate(DateRange::fromStrings('2026-03-01', '2026-03-31'));

    expect($report->totalOrders)->toBe(2)
        ->and($report->totalRevenue)->toEqual(Money::fromDollars(80));
});

test('sales report respects date range', function () {
    createPaidOrder(50.00, '2026-02-15');
    createPaidOrder(30.00, '2026-03-15');

    $report = resolve(SalesReport::class)->generate(DateRange::fromStrings('2026-03-01', '2026-03-31'));

    expect($report->totalOrders)->toBe(1)
        ->and($report->totalRevenue)->toEqual(Money::fromDollars(30));
});

test('customer report returns data for date range', function () {
    $report = resolve(CustomerReport::class)->generate(DateRange::fromStrings('2026-03-01', '2026-03-31'));

    expect($report)->toBeArray();
});

test('product performance report returns data for date range', function () {
    Product::factory()->recycle(test()->category)->create(['name' => 'Sourdough', 'price' => 10.00]);

    $report = resolve(ProductReport::class)->generate(DateRange::fromStrings('2026-03-01', '2026-03-31'));

    expect($report)->toBeInstanceOf(ProductReportResult::class)
        ->and($report->products)->toHaveCount(1);
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

    $report = resolve(FinancialReport::class)->generate(2026);
    $serialized = $report->toArray();

    expect($report->totalRevenue)->toEqual(Money::fromDollars(100))
        ->and($report->totalExpenses)->toEqual(Money::fromDollars(30))
        ->and($report->profit)->toEqual(Money::fromDollars(70))
        ->and($serialized['totalRevenue'])->toBe(100.0)
        ->and($serialized['totalExpenses'])->toBe(30.0)
        ->and($serialized['profit'])->toBe(70.0);
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

    $report = resolve(InventoryReport::class)->generate();

    expect($report['lowStockItems'])->toBe(1);
    $flour = collect($report['ingredients'])->firstWhere('name', 'Flour');
    expect($flour['is_low'])->toBeTrue();
});
