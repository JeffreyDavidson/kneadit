<?php

use App\Filament\Pages\Analytics\ProductTrends;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new ProductTrends;
});

test('mount sets month to current month', function () {
    test()->page->month = 0;
    test()->page->year = 0;
    test()->page->mount();

    expect(test()->page->month)->toBe(now()->month)
        ->and(test()->page->year)->toBe(now()->year);
});

test('mount preserves non-zero month and year', function () {
    test()->page->month = 3;
    test()->page->year = 2025;
    test()->page->mount();

    expect(test()->page->month)->toBe(3)
        ->and(test()->page->year)->toBe(2025);
});

test('previous month decrements month', function () {
    test()->page->month = 6;
    test()->page->year = 2026;

    test()->page->previousMonth();

    expect(test()->page->month)->toBe(5)
        ->and(test()->page->year)->toBe(2026);
});

test('previous month wraps to december of previous year', function () {
    test()->page->month = 1;
    test()->page->year = 2026;

    test()->page->previousMonth();

    expect(test()->page->month)->toBe(12)
        ->and(test()->page->year)->toBe(2025);
});

test('next month increments month', function () {
    test()->page->month = 6;
    test()->page->year = 2026;

    test()->page->nextMonth();

    expect(test()->page->month)->toBe(7)
        ->and(test()->page->year)->toBe(2026);
});

test('next month wraps to january of next year', function () {
    test()->page->month = 12;
    test()->page->year = 2025;

    test()->page->nextMonth();

    expect(test()->page->month)->toBe(1)
        ->and(test()->page->year)->toBe(2026);
});

test('month label property returns formatted string', function () {
    test()->page->month = 4;
    test()->page->year = 2026;

    expect(test()->page->getMonthLabelProperty())->toBe('April 2026');
});

test('prev month label property returns previous month formatted', function () {
    test()->page->month = 4;
    test()->page->year = 2026;

    expect(test()->page->getPrevMonthLabelProperty())->toBe('Mar 2026');
});

test('trends data property returns array', function () {
    test()->page->month = now()->month;
    test()->page->year = now()->year;

    expect(test()->page->getTrendsDataProperty())->toBeArray();
});
