<?php

use App\Reports\Inventory\ProductReport;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates product report for a date range', function () {
    $range = DateRange::forMonth(2026, 3);
    $report = new ProductReport;
    $result = $report->generate($range);

    expect($result)->toBeArray();
});
