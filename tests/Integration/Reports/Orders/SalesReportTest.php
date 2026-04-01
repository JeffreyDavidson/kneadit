<?php

use App\Reports\Orders\SalesReport;
use App\ValueObjects\DateRange;

beforeEach(fn () => setUpTenantTest());

it('returns expected report keys', function () {
    $report = new SalesReport;
    $range = DateRange::fromStrings('2026-01-01', '2026-01-31');

    $result = $report->generate($range);

    expect($result)->toHaveKeys(['totalOrders', 'totalRevenue', 'avgOrderValue', 'ordersByStatus', 'topProducts', 'revenueByDay']);
});
