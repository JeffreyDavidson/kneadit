<?php

use App\Filament\Widgets\UpcomingHolidayWidget;
use App\Models\Operations\Holiday;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

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

test('a holiday on the bakery-local today is still upcoming', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    Holiday::factory()->active()->create(['name' => 'Harvest Day', 'date' => '2026-10-05']);

    $data = test()->widget->getHolidayData();

    expect($data['name'] ?? null)->toBe('Harvest Day');
});
