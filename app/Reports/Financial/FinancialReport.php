<?php

namespace App\Reports\Financial;

use App\DataTransferObjects\Financial\FinancialExpenseBreakdown;
use App\DataTransferObjects\Financial\FinancialReportExpense;
use App\DataTransferObjects\Financial\FinancialReportMonth;
use App\DataTransferObjects\Financial\FinancialReportResult;
use App\DataTransferObjects\Financial\MonthlyFinancials;
use App\Models\Financial\Expense;
use App\Services\Financial\FinancialCalculator;
use App\ValueObjects\Money;

class FinancialReport
{
    public function __construct(
        private readonly FinancialCalculator $calculator,
    ) {}

    public function generate(int $year): FinancialReportResult
    {
        $summary = $this->calculator->calculate($year);

        // expenses.deductible_amount is bigint cents (migration 2026_04_22_230000).
        $deductible = Money::fromCents((int) Expense::query()->whereYear('date', $year)->sum('deductible_amount'));

        $monthly = array_values($summary->monthlyBreakdown->map(fn (MonthlyFinancials $m): FinancialReportMonth => new FinancialReportMonth(
            month: substr($m->monthName, 0, 3),
            revenue: $m->revenue,
            expenses: $m->expenses,
            profit: $m->net,
        ))->all());

        $expensesByCategory = array_values($summary->expenseBreakdown->map(fn (FinancialExpenseBreakdown $e): FinancialReportExpense => new FinancialReportExpense(
            category: $e->category,
            amount: $e->amount,
        ))->all());

        return new FinancialReportResult(
            totalRevenue: $summary->totalRevenue,
            totalExpenses: $summary->totalExpenses,
            profit: $summary->netProfit,
            deductible: $deductible,
            monthly: $monthly,
            expensesByCategory: $expensesByCategory,
        );
    }
}
