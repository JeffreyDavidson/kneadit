<?php

use App\Filament\Pages\Operations\OrderCalendar;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new OrderCalendar;
});

test('mount sets current year and month', function () {
    testFixture('page', OrderCalendar::class)->mount();

    expect(testFixture('page', OrderCalendar::class)->currentYear)->toBe(now()->year)
        ->and(testFixture('page', OrderCalendar::class)->currentMonth)->toBe(now()->month);
});

test('mount initializes selected day orders as empty collection', function () {
    testFixture('page', OrderCalendar::class)->mount();

    expect(testFixture('page', OrderCalendar::class)->selectedDayOrders)->toBeEmpty();
});

test('mount initializes selected date as null', function () {
    testFixture('page', OrderCalendar::class)->mount();

    expect(testFixture('page', OrderCalendar::class)->selectedDate)->toBeNull();
});

test('previous month decrements month', function () {
    testFixture('page', OrderCalendar::class)->mount();
    $originalMonth = testFixture('page', OrderCalendar::class)->currentMonth;
    $originalYear = testFixture('page', OrderCalendar::class)->currentYear;

    testFixture('page', OrderCalendar::class)->previousMonth();

    if ($originalMonth === 1) {
        expect(testFixture('page', OrderCalendar::class)->currentMonth)->toBe(12)
            ->and(testFixture('page', OrderCalendar::class)->currentYear)->toBe($originalYear - 1);
    } else {
        expect(testFixture('page', OrderCalendar::class)->currentMonth)->toBe($originalMonth - 1);
    }
});

test('previous month resets selection', function () {
    testFixture('page', OrderCalendar::class)->mount();
    testFixture('page', OrderCalendar::class)->selectDay('2026-04-15');

    testFixture('page', OrderCalendar::class)->previousMonth();

    expect(testFixture('page', OrderCalendar::class)->selectedDate)->toBeNull()
        ->and(testFixture('page', OrderCalendar::class)->selectedDayOrders)->toBeEmpty();
});

test('next month increments month', function () {
    testFixture('page', OrderCalendar::class)->mount();
    $originalMonth = testFixture('page', OrderCalendar::class)->currentMonth;
    $originalYear = testFixture('page', OrderCalendar::class)->currentYear;

    testFixture('page', OrderCalendar::class)->nextMonth();

    if ($originalMonth === 12) {
        expect(testFixture('page', OrderCalendar::class)->currentMonth)->toBe(1)
            ->and(testFixture('page', OrderCalendar::class)->currentYear)->toBe($originalYear + 1);
    } else {
        expect(testFixture('page', OrderCalendar::class)->currentMonth)->toBe($originalMonth + 1);
    }
});

test('next month resets selection', function () {
    testFixture('page', OrderCalendar::class)->mount();
    testFixture('page', OrderCalendar::class)->selectDay('2026-04-15');

    testFixture('page', OrderCalendar::class)->nextMonth();

    expect(testFixture('page', OrderCalendar::class)->selectedDate)->toBeNull()
        ->and(testFixture('page', OrderCalendar::class)->selectedDayOrders)->toBeEmpty();
});

test('select day sets selected date', function () {
    testFixture('page', OrderCalendar::class)->mount();
    testFixture('page', OrderCalendar::class)->selectDay('2026-04-15');

    expect(testFixture('page', OrderCalendar::class)->selectedDate)->toBe('2026-04-15');
});

test('get calendar days returns collection', function () {
    testFixture('page', OrderCalendar::class)->mount();
    $days = testFixture('page', OrderCalendar::class)->getCalendarDays();

    expect($days)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($days)->not->toBeEmpty();
});

test('get calendar days includes today flag', function () {
    testFixture('page', OrderCalendar::class)->mount();
    $days = testFixture('page', OrderCalendar::class)->getCalendarDays();

    $today = $days->first(fn (mixed $day): bool => is_array($day) && ($day['isToday'] ?? false) === true);

    expect($today)->not->toBeNull();
});

test('get calendar days includes current month flag', function () {
    testFixture('page', OrderCalendar::class)->mount();
    $days = testFixture('page', OrderCalendar::class)->getCalendarDays();

    $currentMonthDays = $days->filter(fn (mixed $day): bool => is_array($day) && ($day['isCurrentMonth'] ?? false) === true);

    expect($currentMonthDays)->not->toBeEmpty();
});

test('get calendar days includes color class', function () {
    testFixture('page', OrderCalendar::class)->mount();
    $days = testFixture('page', OrderCalendar::class)->getCalendarDays();

    $first = $days->firstOrFail();
    throw_unless(is_array($first), RuntimeException::class, 'Expected a calendar day.');
    expect($first['colorClass'] ?? null)->toBeString();
});

test('get current month name returns formatted string', function () {
    testFixture('page', OrderCalendar::class)->currentYear = 2026;
    testFixture('page', OrderCalendar::class)->currentMonth = 4;

    expect(testFixture('page', OrderCalendar::class)->getCurrentMonthName())->toBe('April 2026');
});

test('load order counts populates order counts', function () {
    testFixture('page', OrderCalendar::class)->mount();
    testFixture('page', OrderCalendar::class)->loadOrderCounts();

    expect(testFixture('page', OrderCalendar::class)->orderCounts)->toBeInstanceOf(Illuminate\Support\Collection::class);
});
