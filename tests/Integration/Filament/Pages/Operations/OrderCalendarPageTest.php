<?php

use App\Filament\Pages\Operations\OrderCalendar;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new OrderCalendar;
});

test('mount sets current year and month', function () {
    test()->page->mount();

    expect(test()->page->currentYear)->toBe(now()->year)
        ->and(test()->page->currentMonth)->toBe(now()->month);
});

test('mount initializes selected day orders as empty collection', function () {
    test()->page->mount();

    expect(test()->page->selectedDayOrders)->toBeEmpty();
});

test('mount initializes selected date as null', function () {
    test()->page->mount();

    expect(test()->page->selectedDate)->toBeNull();
});

test('previous month decrements month', function () {
    test()->page->mount();
    $originalMonth = test()->page->currentMonth;
    $originalYear = test()->page->currentYear;

    test()->page->previousMonth();

    if ($originalMonth === 1) {
        expect(test()->page->currentMonth)->toBe(12)
            ->and(test()->page->currentYear)->toBe($originalYear - 1);
    } else {
        expect(test()->page->currentMonth)->toBe($originalMonth - 1);
    }
});

test('previous month resets selection', function () {
    test()->page->mount();
    test()->page->selectedDate = '2026-04-15';

    test()->page->previousMonth();

    expect(test()->page->selectedDate)->toBeNull()
        ->and(test()->page->selectedDayOrders)->toBeEmpty();
});

test('next month increments month', function () {
    test()->page->mount();
    $originalMonth = test()->page->currentMonth;
    $originalYear = test()->page->currentYear;

    test()->page->nextMonth();

    if ($originalMonth === 12) {
        expect(test()->page->currentMonth)->toBe(1)
            ->and(test()->page->currentYear)->toBe($originalYear + 1);
    } else {
        expect(test()->page->currentMonth)->toBe($originalMonth + 1);
    }
});

test('next month resets selection', function () {
    test()->page->mount();
    test()->page->selectedDate = '2026-04-15';

    test()->page->nextMonth();

    expect(test()->page->selectedDate)->toBeNull()
        ->and(test()->page->selectedDayOrders)->toBeEmpty();
});

test('select day sets selected date', function () {
    test()->page->mount();
    test()->page->selectDay('2026-04-15');

    expect(test()->page->selectedDate)->toBe('2026-04-15');
});

test('get calendar days returns collection', function () {
    test()->page->mount();
    $days = test()->page->getCalendarDays();

    expect($days)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($days)->not->toBeEmpty();
});

test('get calendar days includes today flag', function () {
    test()->page->mount();
    $days = test()->page->getCalendarDays();

    $today = $days->first(fn (array $day) => $day['isToday'] === true);

    expect($today)->not->toBeNull();
});

test('get calendar days includes current month flag', function () {
    test()->page->mount();
    $days = test()->page->getCalendarDays();

    $currentMonthDays = $days->filter(fn (array $day) => $day['isCurrentMonth'] === true);

    expect($currentMonthDays)->not->toBeEmpty();
});

test('get calendar days includes color class', function () {
    test()->page->mount();
    $days = test()->page->getCalendarDays();

    expect($days->first()['colorClass'])->toBeString();
});

test('get current month name returns formatted string', function () {
    test()->page->currentYear = 2026;
    test()->page->currentMonth = 4;

    expect(test()->page->getCurrentMonthName())->toBe('April 2026');
});

test('load order counts populates order counts', function () {
    test()->page->mount();
    test()->page->loadOrderCounts();

    expect(test()->page->orderCounts)->toBeInstanceOf(Illuminate\Support\Collection::class);
});
