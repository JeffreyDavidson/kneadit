<?php

use App\Models\Operations\Holiday;
use App\Presenters\HolidayPresenter;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => app()->instance(TenantSettings::class, makeTenantSettings()));

test('isUpcoming returns true for future holidays', function () {
    $holiday = Holiday::factory()->make(['date' => now()->addDays(10)]);

    expect(HolidayPresenter::for($holiday)->isUpcoming())->toBeTrue();
});

test('isUpcoming returns false for past holidays', function () {
    $holiday = Holiday::factory()->make(['date' => now()->subDay()]);

    expect(HolidayPresenter::for($holiday)->isUpcoming())->toBeFalse();
});

test('startPrepBy does not mutate the date attribute', function () {
    $holiday = Holiday::factory()->make([
        'date' => now()->addDays(14),
        'lead_days' => 3,
    ]);

    $originalDate = $holiday->date->toDateString();

    $prepDate = HolidayPresenter::for($holiday)->startPrepBy();

    expect($prepDate->toDateString())->not->toBe($originalDate)
        ->and($holiday->date->toDateString())->toBe($originalDate);
});

test('daysAway calculates correct number of days', function () {
    $holiday = Holiday::factory()->make(['date' => now()->addDays(5)->startOfDay()]);

    expect(HolidayPresenter::for($holiday)->daysAway())->toBe(5);
});

test('daysAwayLabel produces friendly text for each day-distance bucket', function (int $offsetDays, string $expected) {
    $holiday = Holiday::factory()->make(['date' => now()->addDays($offsetDays)->startOfDay()]);

    expect(HolidayPresenter::for($holiday)->daysAwayLabel())->toBe($expected);
})->with([
    'today' => [0, 'Today!'],
    'tomorrow' => [1, 'Tomorrow'],
    'far future' => [12, '12 days away'],
    'past' => [-3, 'Passed 3 days ago'],
]);

test('orderingStatus returns Past when the holiday date has passed', function () {
    $holiday = Holiday::factory()->make(['date' => now()->subDay()]);

    expect(HolidayPresenter::for($holiday)->orderingStatus())->toBe('Past');
});

test('orderingStatus returns Open when far from deadline', function () {
    $holiday = Holiday::factory()->make([
        'date' => now()->addDays(30),
        'order_deadline' => now()->addDays(20),
    ]);

    expect(HolidayPresenter::for($holiday)->orderingStatus())->toBe('Open');
});

test('orderingStatus returns Urgent when deadline within 3 days', function () {
    $holiday = Holiday::factory()->make([
        'date' => now()->addDays(10),
        'order_deadline' => now()->addDays(2),
    ]);

    expect(HolidayPresenter::for($holiday)->orderingStatus())->toBe('Urgent');
});

test('prepStatus returns Passed for past holidays', function () {
    $holiday = Holiday::factory()->make(['date' => now()->subDay()]);

    expect(HolidayPresenter::for($holiday)->prepStatus())->toBe('Passed');
});

test('prepStatus returns Planning when far from holiday', function () {
    $holiday = Holiday::factory()->make([
        'date' => now()->addDays(60),
        'lead_days' => 7,
    ]);

    expect(HolidayPresenter::for($holiday)->prepStatus())->toBe('Planning');
});

test('prepStatus returns Prep Time when inside the prep window', function () {
    $holiday = Holiday::factory()->make([
        'date' => now()->addDays(3),
        'lead_days' => 7,
    ]);

    expect(HolidayPresenter::for($holiday)->prepStatus())->toBe('Prep Time!');
});

test('deadline and day counts follow the bakery-local date', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-12-21 02:00');
    $holiday = Holiday::factory()->make(['date' => '2026-12-25', 'order_deadline' => '2026-12-20']);

    $presenter = HolidayPresenter::for($holiday);

    expect($presenter->isDeadlinePassed())->toBeFalse()
        ->and($presenter->daysUntilDeadline())->toBe(0)
        ->and($presenter->daysAway())->toBe(5);
});

test('the deadline day itself is not passed', function () {
    Date::setTestNow('2026-12-20 15:00');
    $holiday = Holiday::factory()->make(['date' => '2026-12-25', 'order_deadline' => '2026-12-20']);

    expect(HolidayPresenter::for($holiday)->isDeadlinePassed())->toBeFalse();
});
