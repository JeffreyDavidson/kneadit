<?php

use App\Reports\FinancialReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates financial report for a year', function () {
    $report = new FinancialReport;
    $result = $report->generate(2026);

    expect($result)->toBeArray()
        ->toHaveKeys(['totalRevenue', 'totalExpenses', 'profit', 'monthly']);
});
