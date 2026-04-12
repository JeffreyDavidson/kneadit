<?php

use App\Filament\Pages\Operations\HolidayPlanningCalendar;
use App\Models\Operations\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

test('get days away for past holiday', function () {
    $holiday = Holiday::factory()->create(['date' => now()->subDays(5)]);

    $result = test()->page->getDaysAway($holiday);

    expect($result)->toContain('Passed');
});

test('get days away for today', function () {
    $holiday = Holiday::factory()->create(['date' => now()]);

    $result = test()->page->getDaysAway($holiday);

    expect($result)->toBe('Today!');
});

test('get days away result varies by distance', function () {
    $holiday = Holiday::factory()->create(['date' => today()->addDays(5)]);

    $result = test()->page->getDaysAway($holiday);

    expect($result)->toContain('days away');
});

test('get days away for future holiday', function () {
    $holiday = Holiday::factory()->create(['date' => now()->addDays(10)]);

    $result = test()->page->getDaysAway($holiday);

    expect($result)->toContain('days away');
});

test('get status color for past holiday is gray', function () {
    $holiday = Holiday::factory()->create(['date' => now()->subDays(5)]);

    expect(test()->page->getStatusColor($holiday))->toBe('gray');
});

test('get status color for far future holiday is green', function () {
    $holiday = Holiday::factory()->create(['date' => now()->addDays(90), 'lead_days' => 14]);

    expect(test()->page->getStatusColor($holiday))->toBe('green');
});

test('get status text for past holiday', function () {
    $holiday = Holiday::factory()->create(['date' => now()->subDays(5)]);

    expect(test()->page->getStatusText($holiday))->toBe('Passed');
});

test('get status text for far future holiday', function () {
    $holiday = Holiday::factory()->create(['date' => now()->addDays(90), 'lead_days' => 14]);

    expect(test()->page->getStatusText($holiday))->toBe('Planning');
});
