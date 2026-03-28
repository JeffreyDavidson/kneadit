<?php

use App\Reports\InventoryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates inventory report', function () {
    $report = new InventoryReport;
    $result = $report->generate();

    expect($result)->toBeArray();
});
