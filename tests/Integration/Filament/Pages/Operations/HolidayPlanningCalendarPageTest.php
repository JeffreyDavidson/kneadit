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

    expect(test()->page->holidays)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(test()->page->upcomingHolidays)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(test()->page->inPrepPeriod)->toBeInstanceOf(Illuminate\Support\Collection::class);
});

test('get holidays by month groups holidays', function () {
    Holiday::factory()->create(['date' => now()->addDays(10)]);
    Holiday::factory()->create(['date' => now()->addDays(40)]);

    test()->page->loadHolidays();
    $grouped = test()->page->getHolidaysByMonth();

    expect($grouped)->toBeInstanceOf(Illuminate\Support\Collection::class);
});
