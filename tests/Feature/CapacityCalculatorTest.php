<?php

use App\Services\Inventory\CapacityCalculator;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

it('returns default capacity when no limit exists for a date', function () {
    settings(['default_daily_capacity' => 25]);

    $calculator = new CapacityCalculator;
    $max = $calculator->getMaxOrders('2026-04-01');

    expect($max)->toBe(25);
});
