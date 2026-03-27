<?php

use App\Services\Inventory\CapacityCalculator;

beforeEach(fn () => setUpTenantTest());

it('returns default capacity when no limit exists for a date', function () {
    settings(['default_daily_capacity' => 25]);

    $calculator = new CapacityCalculator;
    $max = $calculator->getMaxOrders('2026-04-01');

    expect($max)->toBe(25);
});
