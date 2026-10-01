<?php

use App\Services\Settings\TenantSettings;
use App\ValueObjects\DateRange;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    app()->instance(TenantSettings::class, makeTenantSettings());
});

it('creates a date range from string dates with proper boundaries', function () {
    Date::setTestNow('2026-03-15 14:30:00');

    $range = DateRange::fromStrings('2026-03-01', '2026-03-31');

    expect($range->start->toDateTimeString())->toBe('2026-03-01 00:00:00')->and($range->end->toDateTimeString())->toBe('2026-03-31 23:59:59');
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

    expect($range->start->toDateString())->toBe('2026-03-01')->and($range->end->toDateString())->toBe('2026-03-31')->and($range->start->toTimeString())->toBe('00:00:00')->and($range->end->toTimeString())->toBe('23:59:59');
});

it('creates this year range', function () {
    Date::setTestNow('2026-03-18 14:30:00');

    $range = DateRange::thisYear();

    expect($range->start->toDateString())->toBe('2026-01-01')->and($range->end->toDateString())->toBe('2026-12-31');
});

it('creates last N days range', function () {
    Date::setTestNow('2026-03-18 14:30:00');

    $range = DateRange::lastDays(30);

    expect($range->start->toDateString())->toBe('2026-02-16')->and($range->end->toDateTimeString())->toBe('2026-03-18 23:59:59');
});

it('converts to array for whereBetween', function () {
    Date::setTestNow('2026-03-18 14:30:00');

    $range = DateRange::thisMonth();
    $array = $range->toArray();

    expect($array)->toBeArray()->toHaveCount(2)->and($array[0]->toDateString())->toBe('2026-03-01')->and($array[1]->toDateString())->toBe('2026-03-31');
});

it('creates a range for a specific month', function () {
    $range = DateRange::forMonth(2026, 3);

    expect($range->start->toDateString())->toBe('2026-03-01')->and($range->end->toDateString())->toBe('2026-03-31')->and($range->start->toTimeString())->toBe('00:00:00')->and($range->end->toTimeString())->toBe('23:59:59');
});

it('expresses a bakery-local range in the app timezone without moving the instants', function () {
    $start = Date::parse('2026-10-01 00:00:00', 'America/New_York');
    $end = Date::parse('2026-10-31 23:59:59', 'America/New_York');

    $range = new DateRange($start, $end)->inAppTimezone();

    expect($range->start->toDateTimeString())->toBe('2026-10-01 04:00:00')
        ->and($range->end->toDateTimeString())->toBe('2026-11-01 03:59:59')
        ->and($range->start->equalTo($start))->toBeTrue();
});

describe('bakery-local boundaries', function () {
    beforeEach(function () {
        app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
        Date::setTestNow('2026-10-06 01:00');
    });

    it('starts and ends this week at bakery-local midnight', function () {
        $range = DateRange::thisWeek()->inAppTimezone();

        expect($range->start->toDateTimeString())->toBe('2026-10-05 04:00:00')
            ->and($range->end->toDateTimeString())->toBe('2026-10-12 03:59:59');
    });

    it('starts and ends this month at bakery-local midnight', function () {
        $range = DateRange::thisMonth()->inAppTimezone();

        expect($range->start->toDateTimeString())->toBe('2026-10-01 04:00:00')
            ->and($range->end->toDateTimeString())->toBe('2026-11-01 03:59:59');
    });

    it('starts and ends this year at bakery-local midnight', function () {
        $range = DateRange::thisYear()->inAppTimezone();

        expect($range->start->toDateTimeString())->toBe('2026-01-01 05:00:00')
            ->and($range->end->toDateTimeString())->toBe('2027-01-01 04:59:59');
    });

    it('starts and ends the last days at bakery-local midnight', function () {
        $range = DateRange::lastDays(7)->inAppTimezone();

        expect($range->start->toDateTimeString())->toBe('2026-09-28 04:00:00')
            ->and($range->end->toDateTimeString())->toBe('2026-10-06 03:59:59');
    });

    it('covers a bakery-local day when built from strings', function () {
        $range = DateRange::fromStrings('2026-10-05', '2026-10-05')->inAppTimezone();

        expect($range->start->toDateTimeString())->toBe('2026-10-05 04:00:00')
            ->and($range->end->toDateTimeString())->toBe('2026-10-06 03:59:59');
    });

    it('covers a bakery-local month', function () {
        $range = DateRange::forMonth(2026, 10)->inAppTimezone();

        expect($range->start->toDateTimeString())->toBe('2026-10-01 04:00:00')
            ->and($range->end->toDateTimeString())->toBe('2026-11-01 03:59:59');
    });

    it('keeps the bakery-local calendar days for date column comparisons', function () {
        $range = DateRange::thisWeek();

        expect($range->start->toDateTimeString())->toBe('2026-10-05 00:00:00')
            ->and($range->end->toDateTimeString())->toBe('2026-10-11 23:59:59');
    });
});
