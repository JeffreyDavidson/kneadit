<?php

use App\Reports\Customers\CustomerReport;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates customer report for a date range', function () {
    $range = DateRange::forMonth(2026, 3);
    $report = new CustomerReport;
    $result = $report->generate($range);

    expect($result)->toBeArray()
        ->toHaveKeys(['newCustomers', 'repeatRate', 'topCustomers', 'acquisitionByMonth']);
});
