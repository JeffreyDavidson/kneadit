<?php

use App\DataTransferObjects\Financial\FinancialReportResult;
use App\Reports\Financial\FinancialReport;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates financial report for a year', function () {
    $report = resolve(FinancialReport::class);
    $result = $report->generate(2026);

    expect($result)->toBeInstanceOf(FinancialReportResult::class)
        ->and($result->totalRevenue)->toEqual(Money::zero())
        ->and($result->totalExpenses)->toEqual(Money::zero())
        ->and($result->profit)->toEqual(Money::zero())
        ->and($result->deductible)->toEqual(Money::zero())
        ->and($result->toArray())->toHaveKeys([
            'totalRevenue',
            'totalExpenses',
            'profit',
            'deductible',
            'monthly',
            'expensesByCategory',
        ]);
});
