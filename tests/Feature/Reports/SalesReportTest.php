<?php

use App\Reports\SalesReport;
use App\ValueObjects\DateRange;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
});

it('returns expected report keys', function () {
    $report = new SalesReport;
    $range = DateRange::fromStrings('2026-01-01', '2026-01-31');

    $result = $report->generate($range);

    expect($result)->toHaveKeys(['totalOrders', 'totalRevenue', 'avgOrderValue', 'ordersByStatus', 'topProducts', 'revenueByDay']);
});
