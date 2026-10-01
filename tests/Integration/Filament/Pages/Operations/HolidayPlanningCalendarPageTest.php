<?php

use App\Filament\Pages\Operations\HolidayPlanningCalendar;
use App\Models\Operations\Holiday;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->page = new HolidayPlanningCalendar;
});

test('mount loads holidays', function () {
    Holiday::factory()->create(['name' => 'Christmas', 'date' => now()->addDays(30)]);

    test()->page->mount();

    expect(test()->page->holidays)->toHaveCount(1);
});

test('mount sets upcoming holidays', function () {
    Holiday::factory()->create(['name' => 'Upcoming', 'date' => now()->addDays(10)]);
    Holiday::factory()->create(['name' => 'Past', 'date' => now()->subDays(10)]);

    test()->page->mount();

    expect(test()->page->upcomingHolidays->count())->toBeLessThanOrEqual(10);
});

test('load holidays populates all collections', function () {
    test()->page->loadHolidays();

    expect(test()->page->holidays)->toBeInstanceOf(Collection::class)
        ->and(test()->page->upcomingHolidays)->toBeInstanceOf(Collection::class)
        ->and(test()->page->inPrepPeriod)->toBeInstanceOf(Collection::class);
});

test('get holidays by month groups holidays', function () {
    Holiday::factory()->create(['date' => now()->addDays(10)]);
    Holiday::factory()->create(['date' => now()->addDays(40)]);

    test()->page->loadHolidays();
    $grouped = test()->page->getHolidaysByMonth();

    expect($grouped)->toBeInstanceOf(Collection::class);
});

test('get holidays by month keeps this bakery-local year and the next', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2027-01-01 01:00');
    Holiday::factory()->create(['name' => 'Christmas', 'date' => '2026-12-25']);
    test()->page->loadHolidays();

    $months = test()->page->getHolidaysByMonth();

    expect($months->keys()->all())->toBe(['2026-12']);
});
