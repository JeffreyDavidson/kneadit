<?php

use App\Filament\Pages\Operations\WeeklyPrepPlanner;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new WeeklyPrepPlanner;
});

test('mount sets selected week start to start of current week', function () {
    testFixture('page', WeeklyPrepPlanner::class)->mount();

    expect(testFixture('page', WeeklyPrepPlanner::class)->selectedWeekStart)->toBe(now()->startOfWeek()->format('Y-m-d'));
});

test('mount loads weekly data', function () {
    testFixture('page', WeeklyPrepPlanner::class)->mount();

    expect(testFixture('page', WeeklyPrepPlanner::class)->weeklyOrders)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(testFixture('page', WeeklyPrepPlanner::class)->prepSchedule)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(testFixture('page', WeeklyPrepPlanner::class)->weekDays)->toBeArray();
});

test('load weekly data with null date sets empty collections', function () {
    testFixture('page', WeeklyPrepPlanner::class)->selectedWeekStart = null;

    testFixture('page', WeeklyPrepPlanner::class)->loadWeeklyData();

    expect(testFixture('page', WeeklyPrepPlanner::class)->weeklyOrders)->toBeEmpty()
        ->and(testFixture('page', WeeklyPrepPlanner::class)->prepSchedule)->toBeEmpty();
});

test('load weekly data with valid date populates data', function () {
    testFixture('page', WeeklyPrepPlanner::class)->selectedWeekStart = now()->startOfWeek()->format('Y-m-d');

    testFixture('page', WeeklyPrepPlanner::class)->loadWeeklyData();

    expect(testFixture('page', WeeklyPrepPlanner::class)->weeklyOrders)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get product summary returns collection', function () {
    testFixture('page', WeeklyPrepPlanner::class)->mount();

    expect(testFixture('page', WeeklyPrepPlanner::class)->getProductSummary())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get timeline view returns collection', function () {
    testFixture('page', WeeklyPrepPlanner::class)->mount();

    expect(testFixture('page', WeeklyPrepPlanner::class)->getTimelineView())->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get total prep hours returns float', function () {
    testFixture('page', WeeklyPrepPlanner::class)->mount();

    expect(testFixture('page', WeeklyPrepPlanner::class)->getTotalPrepHours())->toBeFloat();
});

test('get week summary returns array', function () {
    testFixture('page', WeeklyPrepPlanner::class)->mount();

    expect(testFixture('page', WeeklyPrepPlanner::class)->getWeekSummary())->toBeArray();
});
