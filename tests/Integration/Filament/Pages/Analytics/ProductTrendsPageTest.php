<?php

use App\Filament\Pages\Analytics\ProductTrends;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ProductTrends;
});

test('mount sets month to current month', function () {
    testFixture('page', ProductTrends::class)->month = 0;
    testFixture('page', ProductTrends::class)->year = 0;
    testFixture('page', ProductTrends::class)->mount();

    expect(testFixture('page', ProductTrends::class)->month)->toBe(now()->month)
        ->and(testFixture('page', ProductTrends::class)->year)->toBe(now()->year);
});

test('mount preserves non-zero month and year', function () {
    testFixture('page', ProductTrends::class)->month = 3;
    testFixture('page', ProductTrends::class)->year = 2025;
    testFixture('page', ProductTrends::class)->mount();

    expect(testFixture('page', ProductTrends::class)->month)->toBe(3)
        ->and(testFixture('page', ProductTrends::class)->year)->toBe(2025);
});

test('previous month decrements month', function () {
    testFixture('page', ProductTrends::class)->month = 6;
    testFixture('page', ProductTrends::class)->year = 2026;

    testFixture('page', ProductTrends::class)->previousMonth();

    expect(testFixture('page', ProductTrends::class)->month)->toBe(5)
        ->and(testFixture('page', ProductTrends::class)->year)->toBe(2026);
});

test('previous month wraps to december of previous year', function () {
    testFixture('page', ProductTrends::class)->month = 1;
    testFixture('page', ProductTrends::class)->year = 2026;

    testFixture('page', ProductTrends::class)->previousMonth();

    expect(testFixture('page', ProductTrends::class)->month)->toBe(12)
        ->and(testFixture('page', ProductTrends::class)->year)->toBe(2025);
});

test('next month increments month', function () {
    testFixture('page', ProductTrends::class)->month = 6;
    testFixture('page', ProductTrends::class)->year = 2026;

    testFixture('page', ProductTrends::class)->nextMonth();

    expect(testFixture('page', ProductTrends::class)->month)->toBe(7)
        ->and(testFixture('page', ProductTrends::class)->year)->toBe(2026);
});

test('next month wraps to january of next year', function () {
    testFixture('page', ProductTrends::class)->month = 12;
    testFixture('page', ProductTrends::class)->year = 2025;

    testFixture('page', ProductTrends::class)->nextMonth();

    expect(testFixture('page', ProductTrends::class)->month)->toBe(1)
        ->and(testFixture('page', ProductTrends::class)->year)->toBe(2026);
});

test('month label property returns formatted string', function () {
    testFixture('page', ProductTrends::class)->month = 4;
    testFixture('page', ProductTrends::class)->year = 2026;

    expect(testFixture('page', ProductTrends::class)->getMonthLabelProperty())->toBe('April 2026');
});

test('prev month label property returns previous month formatted', function () {
    testFixture('page', ProductTrends::class)->month = 4;
    testFixture('page', ProductTrends::class)->year = 2026;

    expect(testFixture('page', ProductTrends::class)->getPrevMonthLabelProperty())->toBe('Mar 2026');
});

test('trends data property returns array', function () {
    testFixture('page', ProductTrends::class)->month = now()->month;
    testFixture('page', ProductTrends::class)->year = now()->year;

    expect(testFixture('page', ProductTrends::class)->getTrendsDataProperty())->toBeArray();
});
