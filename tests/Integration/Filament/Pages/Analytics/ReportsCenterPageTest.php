<?php

use App\Filament\Pages\Analytics\ReportsCenter;

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

    expect(test()->page->activeReport)->toBe('sales');
});

test('generate report with inventory type', function () {
    test()->page->mount();
    test()->page->generateReport('inventory');

    expect(test()->page->activeReport)->toBe('inventory')
        ->and(test()->page->reportData)->toBeArray();
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
        ->and(test()->page->reportData)->toBeArray();
});
