<?php

namespace App\Reports\Financial;

use App\DataTransferObjects\Financial\FinancialReportResult;
use App\DataTransferObjects\Financial\MonthlyFinancials;
use App\Models\Financial\Expense;
use App\Services\Financial\FinancialCalculator;
use App\ValueObjects\Money;

class FinancialReport
{
    public function __construct(
        private FinancialCalculator $calculator,
    ) {}

    public function generate(int $year): FinancialReportResult
    {
        $summary = $this->calculator->calculate($year);

        // expenses.deductible_amount is bigint cents (migration 2026_04_22_230000).
        $deductible = Money::fromCents((int) Expense::query()->whereYear('date', $year)->sum('deductible_amount'));

        $monthly = array_values($summary->monthlyBreakdown->map(fn (MonthlyFinancials $m): array => [
            'month' => substr($m->monthName, 0, 3),
            'revenue' => Money::fromDollars($m->revenue),
            'expenses' => Money::fromDollars($m->expenses),
            'profit' => Money::fromDollars($m->net),
        ])->all());

        $expensesByCategory = array_values($summary->expenseBreakdown->map(fn (array $e): array => [
            'category' => $e['category'],
            'amount' => Money::fromDollars($e['amount']),
        ])->all());

        return new FinancialReportResult(
            totalRevenue: Money::fromDollars($summary->totalRevenue),
            totalExpenses: Money::fromDollars($summary->totalExpenses),
            profit: Money::fromDollars($summary->netProfit),
            deductible: $deductible,
            monthly: $monthly,
            expensesByCategory: $expensesByCategory,
        );
    }
}
