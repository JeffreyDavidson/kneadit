<?php

use App\DataTransferObjects\Inventory\InventoryReportResult;
use App\Reports\Inventory\InventoryReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates inventory report', function () {
    $report = new InventoryReport;
    $result = $report->generate();

    expect($result)->toBeInstanceOf(InventoryReportResult::class)
        ->and($result->ingredients)->toBeEmpty()
        ->and($result->totalItems)->toBe(0)
        ->and($result->toArray()['ingredients'])->toBeEmpty();
});
