<?php

use App\Filament\Pages\Operations\WeeklyPrepPlanner;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new WeeklyPrepPlanner;
});

test('mount sets selected week start to start of current week', function () {
    test()->page->mount();

    expect(test()->page->selectedWeekStart)->toBe(now()->startOfWeek()->format('Y-m-d'));
});

test('week data loads the selected week', function () {
    test()->page->mount();

    expect(test()->page->weekData->weeklyOrders)->toBeInstanceOf(Collection::class)
        ->and(test()->page->weekData->prepSchedule)->toBeInstanceOf(Collection::class)
        ->and(test()->page->weekData->weekDays)->toHaveCount(7);
});

test('week data without a selected week is empty', function () {
    test()->page->selectedWeekStart = null;

    expect(test()->page->weekData->weeklyOrders)->toBeEmpty()
        ->and(test()->page->weekData->prepSchedule)->toBeEmpty()
        ->and(test()->page->weekData->weekDays)->toBeEmpty();
});

test('get product summary returns collection', function () {
    test()->page->mount();

    expect(test()->page->getProductSummary())->toBeInstanceOf(Collection::class);
});

test('get timeline view returns collection', function () {
    test()->page->mount();

    expect(test()->page->getTimelineView())->toBeInstanceOf(Collection::class);
});

test('get total prep hours returns float', function () {
    test()->page->mount();

    expect(test()->page->getTotalPrepHours())->toBeFloat();
});

test('get week summary returns array', function () {
    test()->page->mount();

    expect(test()->page->getWeekSummary())->toBeArray();
});

test('mount selects the bakery-local week in the Sunday evening', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    // Sunday 2026-10-04, 22:00 in New York; already Monday in UTC.
    Date::setTestNow('2026-10-05 02:00');

    test()->page->mount();

    expect(test()->page->selectedWeekStart)->toBe('2026-09-28');
});
