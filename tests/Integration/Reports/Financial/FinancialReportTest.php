<?php

use App\DataTransferObjects\Financial\FinancialReportExpense;
use App\DataTransferObjects\Financial\FinancialReportMonth;
use App\DataTransferObjects\Financial\FinancialReportResult;
use App\Enums\Financial\ExpenseCategory;
use App\Models\Financial\Expense;
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
        ->and($result->monthly[0])->toBeInstanceOf(FinancialReportMonth::class)
        ->and($result->expensesByCategory)->toBe([])
        ->and($result->toArray())->toHaveKeys([
            'totalRevenue',
            'totalExpenses',
            'profit',
            'deductible',
            'monthly',
            'expensesByCategory',
        ]);
});

test('types expense rows while preserving the serialized report shape', function () {
    Expense::factory()->create([
        'category' => ExpenseCategory::Supplies,
        'amount' => 25.00,
        'deductible_amount' => 25.00,
        'date' => '2026-03-15',
    ]);

    $result = resolve(FinancialReport::class)->generate(2026);

    expect($result->expensesByCategory[0])->toBeInstanceOf(FinancialReportExpense::class)
        ->and($result->expensesByCategory[0]->category)->toBe(ExpenseCategory::Supplies->getLabel())
        ->and($result->expensesByCategory[0]->amount)->toEqual(Money::fromDollars(25))
        ->and($result->toArray()['expensesByCategory'])->toBe([
            ['category' => ExpenseCategory::Supplies->getLabel(), 'amount' => 25.0],
        ]);
});
