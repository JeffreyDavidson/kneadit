<?php

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\CapacityLimit;
use App\Services\Inventory\CapacityCalculator;

beforeEach(fn () => setUpTenantTest());

it('returns default capacity when no limit exists for a date', function () {
    settings(['default_daily_capacity' => 25]);

    $calculator = resolve(CapacityCalculator::class);
    $max = $calculator->getMaxOrders('2026-04-01');

    expect($max)->toBe(25);
});

it('applies a weekday limit to every matching weekday', function () {
    settings(['default_daily_capacity' => 25]);
    CapacityLimit::factory()->weekday(DayOfWeek::Monday)->create(['max_orders' => 8]);

    $calculator = resolve(CapacityCalculator::class);

    expect($calculator->getMaxOrders('2026-10-05'))->toBe(8)
        ->and($calculator->getMaxOrders('2026-10-12'))->toBe(8)
        ->and($calculator->getMaxOrders('2026-10-06'))->toBe(25);
});

it('prefers a specific-date limit over the weekday limit', function () {
    CapacityLimit::factory()->weekday(DayOfWeek::Monday)->create(['max_orders' => 8]);
    CapacityLimit::factory()->specificDate('2026-10-12')->create(['max_orders' => 3]);

    $calculator = resolve(CapacityCalculator::class);

    expect($calculator->getMaxOrders('2026-10-12'))->toBe(3)
        ->and($calculator->getMaxOrders('2026-10-05'))->toBe(8);
});

it('treats a blocked limit as full', function (CapacityLimit $blockedLimit) {
    settings(['default_daily_capacity' => 25]);

    $calculator = resolve(CapacityCalculator::class);

    expect($calculator->forDate('2026-10-05')?->is($blockedLimit))->toBeTrue()
        ->and($calculator->isAvailable('2026-10-05'))->toBeFalse()
        ->and($calculator->remainingSlots('2026-10-05'))->toBe(0)
        ->and($calculator->getMaxOrders('2026-10-05'))->toBe(0);
})->with([
    'blocked weekday' => fn () => CapacityLimit::factory()->weekday(DayOfWeek::Monday)->blocked()->create(),
    'blocked date' => fn () => CapacityLimit::factory()->specificDate('2026-10-05')->blocked()->create(),
]);
