<?php

use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\Queries\Scheduling\DateOpenStatusQuery;
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

    $status = DateOpenStatusQuery::forDate($date);

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

    $status = DateOpenStatusQuery::forDate($date);

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

    expect(DateOpenStatusQuery::forDate($date)->open)->toBeTrue();
});

test('returns closed when BusinessSchedule for the day is not open', function () {
    $date = Date::parse('2026-05-03'); // Sunday

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => false,
    ]);

    $status = DateOpenStatusQuery::forDate($date);

    expect($status->open)->toBeFalse()
        ->and($status->reason)->toBe('Closed');
});

test('returns open when no BusinessSchedule exists for the day of week', function () {
    $date = Date::parse('2026-05-03');

    $status = DateOpenStatusQuery::forDate($date);

    expect($status->open)->toBeTrue()
        ->and($status->reason)->toBeNull();
});

test('returns open when date is not blocked and schedule is open', function () {
    $date = Date::parse('2026-05-04');

    BusinessSchedule::factory()->create([
        'day_of_week' => $date->dayOfWeek,
        'is_open' => true,
    ]);

    $status = DateOpenStatusQuery::forDate($date);

    expect($status->open)->toBeTrue()
        ->and($status->reason)->toBeNull();
});

test('accepts string dates', function () {
    BusinessSchedule::factory()->create([
        'day_of_week' => (int) Date::parse('2026-05-04')->dayOfWeek,
        'is_open' => true,
    ]);

    expect(DateOpenStatusQuery::forDate('2026-05-04')->open)->toBeTrue();
});
