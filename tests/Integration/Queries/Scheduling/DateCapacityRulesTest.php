<?php

use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\Models\Operations\Holiday;
use App\Queries\Scheduling\DateCapacityRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns blocked with reason when an all-day BlockedDate matches', function () {
    $date = Date::parse('2026-12-25');

    BlockedDate::factory()->create([
        'date' => $date->toDateString(),
        'is_all_day' => true,
        'reason' => 'Christmas',
    ]);

    $status = DateCapacityRules::between($date, $date)->status($date);

    expect($status->open)->toBeFalse()
        ->and($status->reason)->toBe('Christmas');
});

test('returns blocked with default reason when BlockedDate has no reason', function () {
    $date = Date::parse('2026-07-04');

    BlockedDate::factory()->create([
        'date' => $date->toDateString(),
        'is_all_day' => true,
        'reason' => null,
    ]);

    $status = DateCapacityRules::between($date, $date)->status($date);

    expect($status->open)->toBeFalse()
        ->and($status->reason)->toBe('Blocked');
});

test('partial-day BlockedDate does not close the date', function () {
    $date = Date::parse('2026-06-15');

    BlockedDate::factory()->create([
        'date' => $date->toDateString(),
        'is_all_day' => false,
        'reason' => 'Afternoon only',
    ]);

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
    ]);

    expect(DateCapacityRules::between($date, $date)->status($date)->open)->toBeTrue();
});

test('returns closed when BusinessSchedule for the day is not open', function () {
    $date = Date::parse('2026-05-03'); // Sunday

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => false,
    ]);

    $status = DateCapacityRules::between($date, $date)->status($date);

    expect($status->open)->toBeFalse()
        ->and($status->reason)->toBe('Closed');
});

test('returns open when no BusinessSchedule exists for the day of week', function () {
    $date = Date::parse('2026-05-03');

    $status = DateCapacityRules::between($date, $date)->status($date);

    expect($status->open)->toBeTrue()
        ->and($status->reason)->toBeNull();
});

test('returns open when date is not blocked and schedule is open', function () {
    $date = Date::parse('2026-05-04');

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
    ]);

    $status = DateCapacityRules::between($date, $date)->status($date);

    expect($status->open)->toBeTrue()
        ->and($status->reason)->toBeNull();
});

test('closes a holiday date once its order deadline has passed', function () {
    Date::setTestNow('2026-12-21 09:00');
    $christmas = Date::parse('2026-12-25');
    Holiday::factory()->active()->create([
        'name' => 'Christmas',
        'date' => '2026-12-25',
        'order_deadline' => '2026-12-20',
    ]);

    $status = DateCapacityRules::between($christmas, $christmas)->status($christmas);

    expect($status->open)->toBeFalse()
        ->and($status->reason)->toBe('Orders closed for Christmas');
});

test('keeps a holiday date open while its order deadline does not apply', function (array $overrides) {
    Date::setTestNow('2026-12-20 23:30');
    $christmas = Date::parse('2026-12-25');
    Holiday::factory()->active()->create([
        'date' => '2026-12-25',
        'order_deadline' => '2026-12-20',
        ...$overrides,
    ]);

    $status = DateCapacityRules::between($christmas, $christmas)->status($christmas);

    expect($status->open)->toBeTrue();
})->with([
    'on the deadline day' => [[]],
    'before the deadline' => [['order_deadline' => '2026-12-22']],
    'inactive holiday past its deadline' => [['is_active' => false, 'order_deadline' => '2026-12-18']],
    'holiday without a deadline' => [['order_deadline' => null]],
]);
