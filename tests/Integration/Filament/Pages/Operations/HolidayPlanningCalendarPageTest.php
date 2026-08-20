<?php

use App\Filament\Pages\Operations\HolidayPlanningCalendar;
use App\Models\Operations\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->page = new HolidayPlanningCalendar;
});

test('mount loads holidays', function () {
    Holiday::factory()->create(['name' => 'Christmas', 'date' => now()->addDays(30)]);

    testFixture('page', HolidayPlanningCalendar::class)->mount();

    expect(testFixture('page', HolidayPlanningCalendar::class)->holidays)->toHaveCount(1);
});

test('mount sets upcoming holidays', function () {
    Holiday::factory()->create(['name' => 'Upcoming', 'date' => now()->addDays(10)]);
    Holiday::factory()->create(['name' => 'Past', 'date' => now()->subDays(10)]);

    testFixture('page', HolidayPlanningCalendar::class)->mount();

    expect(testFixture('page', HolidayPlanningCalendar::class)->upcomingHolidays->count())->toBeLessThanOrEqual(10);
});

test('load holidays populates all collections', function () {
    testFixture('page', HolidayPlanningCalendar::class)->loadHolidays();

    expect(testFixture('page', HolidayPlanningCalendar::class)->holidays)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(testFixture('page', HolidayPlanningCalendar::class)->upcomingHolidays)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(testFixture('page', HolidayPlanningCalendar::class)->inPrepPeriod)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get holidays by month groups holidays', function () {
    Holiday::factory()->create(['date' => now()->addDays(10)]);
    Holiday::factory()->create(['date' => now()->addDays(40)]);

    testFixture('page', HolidayPlanningCalendar::class)->loadHolidays();
    $grouped = testFixture('page', HolidayPlanningCalendar::class)->getHolidaysByMonth();

    expect($grouped)->toBeInstanceOf(Illuminate\Support\Collection::class);
});
