<?php

use App\Filament\Widgets\UpcomingHolidayWidget;
use App\Models\Operations\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->widget = new UpcomingHolidayWidget;
});

test('get stats returns empty when no holidays', function () {
    $method = new ReflectionMethod(UpcomingHolidayWidget::class, 'getStats');
    expect($method->invoke(test()->widget))->toBeEmpty();
});

test('get stats returns empty when no upcoming holidays', function () {
    Holiday::factory()->create(['date' => now()->subDays(5), 'is_active' => true]);

    $method = new ReflectionMethod(UpcomingHolidayWidget::class, 'getStats');
    expect($method->invoke(test()->widget))->toBeEmpty();
});

test('get stats returns stat for upcoming holiday', function () {
    Holiday::factory()->create([
        'name' => "Valentine's Day",
        'date' => now()->addDays(10),
        'order_deadline' => now()->addDays(5),
        'is_active' => true,
    ]);

    $method = new ReflectionMethod(UpcomingHolidayWidget::class, 'getStats');
    $stats = $method->invoke(test()->widget);

    expect($stats)->toHaveCount(1);
});

test('get stats includes holiday name in stat', function () {
    Holiday::factory()->create([
        'name' => 'Easter',
        'date' => now()->addDays(20),
        'order_deadline' => now()->addDays(15),
        'is_active' => true,
    ]);

    $method = new ReflectionMethod(UpcomingHolidayWidget::class, 'getStats');
    $stats = $method->invoke(test()->widget);

    expect($stats[0]->getLabel())->toContain('Easter');
});
