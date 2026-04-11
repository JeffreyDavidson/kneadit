<?php

use App\Services\Customers\BirthdayCalculator;
use Illuminate\Support\Facades\Date;

// ──────────────────────────────────────────────────────────
// hasBirthday
// ──────────────────────────────────────────────────────────

it('returns true when birthday is set', function () {
    $calculator = new BirthdayCalculator;

    expect($calculator->hasBirthday(Date::parse('1990-06-15')))->toBeTrue();
});

it('returns false when birthday is null', function () {
    $calculator = new BirthdayCalculator;

    expect($calculator->hasBirthday(null))->toBeFalse();
});

// ──────────────────────────────────────────────────────────
// isThisMonth
// ──────────────────────────────────────────────────────────

it('returns true when birthday is this month', function () {
    Date::setTestNow('2026-03-15');

    $calculator = new BirthdayCalculator;

    expect($calculator->isThisMonth(Date::parse('1990-03-26')))->toBeTrue();
});

it('returns false when birthday is a different month', function () {
    Date::setTestNow('2026-03-15');

    $calculator = new BirthdayCalculator;

    expect($calculator->isThisMonth(Date::parse('1990-07-10')))->toBeFalse();
});

it('returns false for isThisMonth when birthday is null', function () {
    Date::setTestNow('2026-03-15');

    $calculator = new BirthdayCalculator;

    expect($calculator->isThisMonth(null))->toBeFalse();
});

// ──────────────────────────────────────────────────────────
// isToday
// ──────────────────────────────────────────────────────────

it('detects today is a birthday', function () {
    Date::setTestNow('2026-03-26');

    $calculator = new BirthdayCalculator;

    expect($calculator->isToday(Date::parse('1990-03-26')))->toBeTrue()
        ->and($calculator->isToday(Date::parse('1990-04-15')))->toBeFalse()
        ->and($calculator->isToday(null))->toBeFalse();
});

// ──────────────────────────────────────────────────────────
// daysUntil
// ──────────────────────────────────────────────────────────

it('returns null when birthday is null', function () {
    $calculator = new BirthdayCalculator;

    expect($calculator->daysUntil(null))->toBeNull();
});

it('returns 0 when birthday is today', function () {
    Date::setTestNow('2026-06-15');

    $calculator = new BirthdayCalculator;

    expect($calculator->daysUntil(Date::parse('1990-06-15')))->toBe(0);
});

it('returns correct days for a future birthday this year', function () {
    Date::setTestNow('2026-06-01');

    $calculator = new BirthdayCalculator;

    expect($calculator->daysUntil(Date::parse('1990-06-15')))->toBe(14);
});

it('wraps to next year for a birthday that already passed', function () {
    Date::setTestNow('2026-06-20');

    $calculator = new BirthdayCalculator;

    // June 15 already passed, so next occurrence is 2027-06-15 = 360 days away
    expect($calculator->daysUntil(Date::parse('1990-06-15')))->toBe(360);
});
