<?php

use App\Reports\Financial\FinancialReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates financial report for a year', function () {
    $report = resolve(FinancialReport::class);
    $result = $report->generate(2026);

    expect($result)->toBeArray()
        ->toHaveKeys(['totalRevenue', 'totalExpenses', 'profit', 'monthly']);
});
