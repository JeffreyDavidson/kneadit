<?php

use App\Filament\Pages\Operations\WeeklyPrepPlanner;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new WeeklyPrepPlanner;
});

test('mount sets selected week start to start of current week', function () {
    test()->page->mount();

    expect(test()->page->selectedWeekStart)->toBe(now()->startOfWeek()->format('Y-m-d'));
});

test('mount loads weekly data', function () {
    test()->page->mount();

    expect(test()->page->weeklyOrders)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(test()->page->prepSchedule)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(test()->page->weekDays)->toBeArray();
});

test('load weekly data with null date sets empty collections', function () {
    test()->page->selectedWeekStart = null;

    test()->page->loadWeeklyData();

    expect(test()->page->weeklyOrders)->toBeEmpty()
        ->and(test()->page->prepSchedule)->toBeEmpty();
});

test('load weekly data with valid date populates data', function () {
    test()->page->selectedWeekStart = now()->startOfWeek()->format('Y-m-d');

    test()->page->loadWeeklyData();

    expect(test()->page->weeklyOrders)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get product summary returns collection', function () {
    test()->page->mount();

    expect(test()->page->getProductSummary())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get timeline view returns collection', function () {
    test()->page->mount();

    expect(test()->page->getTimelineView())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get total prep hours returns float', function () {
    test()->page->mount();

    expect(test()->page->getTotalPrepHours())->toBeFloat();
});

test('get week summary returns array', function () {
    test()->page->mount();

    expect(test()->page->getWeekSummary())->toBeArray();
});
