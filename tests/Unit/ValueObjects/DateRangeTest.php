<?php

use App\ValueObjects\DateRange;
use Illuminate\Support\Facades\Date;

it('creates a date range from string dates with proper boundaries', function () {
    Date::setTestNow('2026-03-15 14:30:00');

    $range = DateRange::fromStrings('2026-03-01', '2026-03-31');

    expect($range->start->toDateTimeString())->toBe('2026-03-01 00:00:00');
    expect($range->end->toDateTimeString())->toBe('2026-03-31 23:59:59');
});

it('creates this week range', function () {
    Date::setTestNow('2026-03-18 14:30:00'); // Wednesday

    $range = DateRange::thisWeek();

    expect($range->start->toDateString())->toBe('2026-03-16'); // Monday
    expect($range->end->toDateString())->toBe('2026-03-22'); // Sunday
    expect($range->start->toTimeString())->toBe('00:00:00');
    expect($range->end->toTimeString())->toBe('23:59:59');
});

it('creates this month range', function () {
    Date::setTestNow('2026-03-18 14:30:00');

    $range = DateRange::thisMonth();

    expect($range->start->toDateString())->toBe('2026-03-01');
    expect($range->end->toDateString())->toBe('2026-03-31');
    expect($range->start->toTimeString())->toBe('00:00:00');
    expect($range->end->toTimeString())->toBe('23:59:59');
});

it('creates this year range', function () {
    Date::setTestNow('2026-03-18 14:30:00');

    $range = DateRange::thisYear();

    expect($range->start->toDateString())->toBe('2026-01-01');
    expect($range->end->toDateString())->toBe('2026-12-31');
});

it('creates last N days range', function () {
    Date::setTestNow('2026-03-18 14:30:00');

    $range = DateRange::lastDays(30);

    expect($range->start->toDateString())->toBe('2026-02-16');
    expect($range->end->toDateTimeString())->toBe('2026-03-18 23:59:59');
});

it('converts to array for whereBetween', function () {
    Date::setTestNow('2026-03-18 14:30:00');

    $range = DateRange::thisMonth();
    $array = $range->toArray();

    expect($array)->toBeArray();
    expect($array)->toHaveCount(2);
    expect($array[0]->toDateString())->toBe('2026-03-01');
    expect($array[1]->toDateString())->toBe('2026-03-31');
});

it('creates a range for a specific month', function () {
    $range = DateRange::forMonth(2026, 3);

    expect($range->start->toDateString())->toBe('2026-03-01');
    expect($range->end->toDateString())->toBe('2026-03-31');
    expect($range->start->toTimeString())->toBe('00:00:00');
    expect($range->end->toTimeString())->toBe('23:59:59');
});
