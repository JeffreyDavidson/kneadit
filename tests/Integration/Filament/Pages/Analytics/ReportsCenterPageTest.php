<?php

use App\Filament\Pages\Analytics\ReportsCenter;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ReportsCenter;
});

test('active report defaults to empty', function () {
    expect(testFixture('page', ReportsCenter::class)->activeReport)->toBeEmpty();
});

test('report data defaults to empty array', function () {
    expect(testFixture('page', ReportsCenter::class)->reportData)->toBeEmpty();
});

test('mount sets start date to first of month', function () {
    testFixture('page', ReportsCenter::class)->mount();

    expect(testFixture('page', ReportsCenter::class)->startDate)->toBe(now()->startOfMonth()->format('Y-m-d'));
});

test('mount sets end date to today', function () {
    testFixture('page', ReportsCenter::class)->mount();

    expect(testFixture('page', ReportsCenter::class)->endDate)->toBe(now()->format('Y-m-d'));
});

test('mount sets selected year to current year', function () {
    testFixture('page', ReportsCenter::class)->mount();

    expect(testFixture('page', ReportsCenter::class)->selectedYear)->toBe(now()->year);
});

test('generate report sets active report type', function () {
    testFixture('page', ReportsCenter::class)->mount();
    testFixture('page', ReportsCenter::class)->generateReport('sales');

    expect(testFixture('page', ReportsCenter::class)->activeReport)->toBe('sales');
});

test('generate report with inventory type', function () {
    testFixture('page', ReportsCenter::class)->mount();
    testFixture('page', ReportsCenter::class)->generateReport('inventory');

    expect(testFixture('page', ReportsCenter::class)->activeReport)->toBe('inventory')
        ->and(testFixture('page', ReportsCenter::class)->reportData)->toBeArray();
});

test('generate report with unknown type returns empty', function () {
    testFixture('page', ReportsCenter::class)->mount();
    testFixture('page', ReportsCenter::class)->generateReport('unknown_type');

    expect(testFixture('page', ReportsCenter::class)->reportData)->toBeEmpty();
});

test('generate report with customers type', function () {
    testFixture('page', ReportsCenter::class)->mount();
    testFixture('page', ReportsCenter::class)->generateReport('customers');

    expect(testFixture('page', ReportsCenter::class)->activeReport)->toBe('customers')
        ->and(testFixture('page', ReportsCenter::class)->reportData)->toBeArray();
});

test('generate report with products type', function () {
    testFixture('page', ReportsCenter::class)->mount();
    testFixture('page', ReportsCenter::class)->generateReport('products');

    expect(testFixture('page', ReportsCenter::class)->activeReport)->toBe('products')
        ->and(testFixture('page', ReportsCenter::class)->reportData)->toBeArray();
});

test('generate report with financial type', function () {
    testFixture('page', ReportsCenter::class)->mount();
    testFixture('page', ReportsCenter::class)->generateReport('financial');

    expect(testFixture('page', ReportsCenter::class)->activeReport)->toBe('financial')
        ->and(testFixture('page', ReportsCenter::class)->reportData)->toBeArray();
});
