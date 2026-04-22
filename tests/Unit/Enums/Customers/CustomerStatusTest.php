<?php

use App\Enums\Customers\CustomerStatus;
use Illuminate\Support\Carbon;

beforeEach(fn () => Carbon::setTestNow('2026-04-22 12:00:00'));

afterEach(fn () => Carbon::setTestNow());

test('returns Active when the customer has zero orders', function () {
    expect(CustomerStatus::resolve(orderCount: 0, lastOrderDate: null))->toBe(CustomerStatus::Active);
});

test('returns Active when the customer has orders but no last_order_date', function () {
    expect(CustomerStatus::resolve(orderCount: 5, lastOrderDate: null))->toBe(CustomerStatus::Active);
});

test('returns Active when the most recent order is within the threshold window', function () {
    $lastOrder = Carbon::parse('2026-04-15'); // 7 days ago

    expect(CustomerStatus::resolve(orderCount: 3, lastOrderDate: $lastOrder, thresholdDays: 30))
        ->toBe(CustomerStatus::Active);
});

test('returns AtRisk when the most recent order is beyond the threshold window', function () {
    $lastOrder = Carbon::parse('2026-03-15'); // 38 days ago

    expect(CustomerStatus::resolve(orderCount: 3, lastOrderDate: $lastOrder, thresholdDays: 30))
        ->toBe(CustomerStatus::AtRisk);
});

test('falls back to config-driven threshold when none is passed', function () {
    config(['analytics.at_risk_threshold_days' => 14]);
    $lastOrder = Carbon::parse('2026-04-04'); // 18 days ago — outside 14-day window

    expect(CustomerStatus::resolve(orderCount: 1, lastOrderDate: $lastOrder))
        ->toBe(CustomerStatus::AtRisk);
});

test('exposes Filament HasColor', function () {
    expect(CustomerStatus::Active->getColor())->toBe('success')
        ->and(CustomerStatus::AtRisk->getColor())->toBe('danger');
});

test('exposes Filament HasLabel', function () {
    expect(CustomerStatus::Active->getLabel())->toBe('Active')
        ->and(CustomerStatus::AtRisk->getLabel())->toBe('At Risk');
});
