<?php

use App\Services\Customers\BirthdayCalculator;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => app()->instance(TenantSettings::class, makeTenantSettings()));

// ──────────────────────────────────────────────────────────
// hasBirthday
// ──────────────────────────────────────────────────────────

it('returns true when birthday is set', function () {
    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->hasBirthday(Date::parse('1990-06-15')))->toBeTrue();
});

it('returns false when birthday is null', function () {
    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->hasBirthday(null))->toBeFalse();
});

// ──────────────────────────────────────────────────────────
// isThisMonth
// ──────────────────────────────────────────────────────────

it('returns true when birthday is this month', function () {
    Date::setTestNow('2026-03-15');

    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->isThisMonth(Date::parse('1990-03-26')))->toBeTrue();
});

it('returns false when birthday is a different month', function () {
    Date::setTestNow('2026-03-15');

    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->isThisMonth(Date::parse('1990-07-10')))->toBeFalse();
});

it('returns false for isThisMonth when birthday is null', function () {
    Date::setTestNow('2026-03-15');

    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->isThisMonth(null))->toBeFalse();
});

// ──────────────────────────────────────────────────────────
// isToday
// ──────────────────────────────────────────────────────────

it('detects today is a birthday', function () {
    Date::setTestNow('2026-03-26');

    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->isToday(Date::parse('1990-03-26')))->toBeTrue()
        ->and($calculator->isToday(Date::parse('1990-04-15')))->toBeFalse()
        ->and($calculator->isToday(null))->toBeFalse();
});

// ──────────────────────────────────────────────────────────
// daysUntil
// ──────────────────────────────────────────────────────────

it('returns null when birthday is null', function () {
    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->daysUntil(null))->toBeNull();
});

it('returns 0 when birthday is today', function () {
    Date::setTestNow('2026-06-15');

    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->daysUntil(Date::parse('1990-06-15')))->toBe(0);
});

it('returns correct days for a future birthday this year', function () {
    Date::setTestNow('2026-06-01');

    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->daysUntil(Date::parse('1990-06-15')))->toBe(14);
});

it('wraps to next year for a birthday that already passed', function () {
    Date::setTestNow('2026-06-20');

    $calculator = resolve(BirthdayCalculator::class);

    // June 15 already passed, so next occurrence is 2027-06-15 = 360 days away
    expect($calculator->daysUntil(Date::parse('1990-06-15')))->toBe(360);
});

// ──────────────────────────────────────────────────────────
// Bakery-local date
// ──────────────────────────────────────────────────────────

// 2026-10-06 01:00 UTC is Monday 2026-10-05 21:00 in New York.
test('uses the bakery-local date for the month and day checks', function (string $nowUtc, string $birthday, bool $isThisMonth, bool $isToday) {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow($nowUtc);

    $calculator = resolve(BirthdayCalculator::class);

    expect($calculator->isThisMonth(Date::parse($birthday)))->toBe($isThisMonth)
        ->and($calculator->isToday(Date::parse($birthday)))->toBe($isToday);
})->with([
    'birthday on the local day' => ['2026-10-06 01:00', '1990-10-05', true, true],
    'birthday on the UTC day' => ['2026-10-06 01:00', '1990-10-06', true, false],
    'birthday on the last local day of the month' => ['2026-11-01 02:00', '1990-10-31', true, true],
    'birthday on the first UTC day of the month' => ['2026-11-01 02:00', '1990-11-01', false, false],
]);

test('counts the days until a birthday from the bakery-local date', function (string $birthday, int $expected) {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    $days = resolve(BirthdayCalculator::class)->daysUntil(Date::parse($birthday));

    expect($days)->toBe($expected);
})->with([
    'birthday on the local day' => ['1990-10-05', 0],
    'birthday on the UTC day' => ['1990-10-06', 1],
    'birthday a week out' => ['1990-10-12', 7],
    'birthday the local day before' => ['1990-10-04', 364],
]);

test('counts a birthday today as zero days away at any time of day', function () {
    Date::setTestNow('2026-06-15 15:30');

    $days = resolve(BirthdayCalculator::class)->daysUntil(Date::parse('1990-06-15'));

    expect($days)->toBe(0);
});
