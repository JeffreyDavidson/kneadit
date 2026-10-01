<?php

use App\Filament\Pages\Analytics\ReportsCenter;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ReportsCenter;
});

test('active report defaults to empty', function () {
    expect(test()->page->activeReport)->toBeEmpty();
});

test('report data defaults to empty array', function () {
    expect(test()->page->reportData)->toBeEmpty();
});

test('mount sets start date to first of month', function () {
    test()->page->mount();

    expect(test()->page->startDate)->toBe(now()->startOfMonth()->format('Y-m-d'));
});

test('mount sets end date to today', function () {
    test()->page->mount();

    expect(test()->page->endDate)->toBe(now()->format('Y-m-d'));
});

test('mount sets selected year to current year', function () {
    test()->page->mount();

    expect(test()->page->selectedYear)->toBe(now()->year);
});

test('generate report sets active report type', function () {
    test()->page->mount();
    test()->page->generateReport('sales');

    expect(test()->page->activeReport)->toBe('sales')
        ->and(test()->page->reportData)->toHaveKeys([
            'totalOrders',
            'totalRevenue',
            'avgOrderValue',
            'ordersByStatus',
            'topProducts',
            'revenueByDay',
        ]);
});

test('generate report with inventory type', function () {
    Config::set('analytics.inventory_usage_window_days', 10);

    test()->page->mount();
    test()->page->inventoryUsageWindowDays = 7;
    test()->page->generateReport('inventory');

    expect(test()->page->activeReport)->toBe('inventory')
        ->and(test()->page->reportData)->toBeArray()
        ->and(test()->page->reportData['usageWindowDays'])->toBe(7);
});

test('falls back to the configured window when an unsupported period is selected', function () {
    Config::set('analytics.inventory_usage_window_days', 30);

    test()->page->mount();
    test()->page->inventoryUsageWindowDays = 14;
    test()->page->generateReport('inventory');

    expect(test()->page->inventoryUsageWindowDays)->toBe(30)
        ->and(test()->page->reportData['usageWindowDays'])->toBe(30);
});

test('generate report with unknown type returns empty', function () {
    test()->page->mount();
    test()->page->generateReport('unknown_type');

    expect(test()->page->reportData)->toBeEmpty();
});

test('generate report with customers type', function () {
    test()->page->mount();
    test()->page->generateReport('customers');

    expect(test()->page->activeReport)->toBe('customers')
        ->and(test()->page->reportData)->toBeArray();
});

test('generate report with products type', function () {
    test()->page->mount();
    test()->page->generateReport('products');

    expect(test()->page->activeReport)->toBe('products')
        ->and(test()->page->reportData)->toBeArray();
});

test('generate report with financial type', function () {
    test()->page->mount();
    test()->page->generateReport('financial');

    expect(test()->page->activeReport)->toBe('financial')
        ->and(test()->page->reportData)->toHaveKeys([
            'totalRevenue',
            'totalExpenses',
            'profit',
            'deductible',
            'monthly',
            'expensesByCategory',
        ]);
});

test('generate report with rfm type', function () {
    test()->page->mount();
    test()->page->generateReport('rfm');

    expect(test()->page->activeReport)->toBe('rfm')
        ->and(test()->page->reportData)->toHaveKeys(['total', 'segments']);
});

test('mount defaults the report range and year to the bakery-local date', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2027-01-01 01:00');

    test()->page->mount();

    expect(test()->page->startDate)->toBe('2026-12-01')
        ->and(test()->page->endDate)->toBe('2026-12-31')
        ->and(test()->page->selectedYear)->toBe(2026);
});

test('the customers report covers the bakery-local days of the selected range', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Customer::factory()->create(['created_at' => '2026-10-06 00:30:00']);
    test()->page->mount();
    test()->page->startDate = '2026-10-05';
    test()->page->endDate = '2026-10-05';

    test()->page->generateReport('customers');
    $onTheDay = test()->page->reportData['newCustomers'];
    test()->page->startDate = '2026-10-06';
    test()->page->endDate = '2026-10-06';
    test()->page->generateReport('customers');
    $nextDay = test()->page->reportData['newCustomers'];

    expect($onTheDay)->toBe(1)
        ->and($nextDay)->toBe(0);
});

test('the sales report keeps delivery dates on the selected bakery-local days', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Order::factory()->paid()->create(['delivery_date' => '2026-10-05', 'total' => 40]);
    test()->page->mount();
    test()->page->startDate = '2026-10-05';
    test()->page->endDate = '2026-10-05';

    test()->page->generateReport('sales');
    $onTheDay = test()->page->reportData['totalOrders'];
    test()->page->startDate = '2026-10-06';
    test()->page->endDate = '2026-10-06';
    test()->page->generateReport('sales');
    $nextDay = test()->page->reportData['totalOrders'];

    expect($onTheDay)->toBe(1)
        ->and($nextDay)->toBe(0);
});
