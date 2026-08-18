<?php

use App\Filament\Widgets\UpcomingHolidayWidget;
use App\Models\Operations\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Cache::flush();
    test()->widget = new UpcomingHolidayWidget;
});

test('getHolidayData returns null when no holidays', function () {
    expect(test()->widget->getHolidayData())->toBeNull();
});

test('getHolidayData returns null when no upcoming holidays', function () {
    Holiday::factory()->create(['date' => now()->subDays(5), 'is_active' => true]);

    expect(test()->widget->getHolidayData())->toBeNull();
});

test('getHolidayData returns data array for the next upcoming holiday', function () {
    Holiday::factory()->create([
        'name' => "Valentine's Day",
        'date' => now()->addDays(10),
        'order_deadline' => now()->addDays(5),
        'is_active' => true,
    ]);

    expect(test()->widget->getHolidayData())
        ->toBeArray()
        ->toHaveKeys(['name', 'date', 'orders', 'days_until_deadline', 'deadline_passed', 'is_urgent']);
});

test('getHolidayData includes the holiday name', function () {
    Holiday::factory()->create([
        'name' => 'Easter',
        'date' => now()->addDays(20),
        'order_deadline' => now()->addDays(15),
        'is_active' => true,
    ]);

    expect(test()->widget->getHolidayData()['name'])->toBe('Easter');
});
