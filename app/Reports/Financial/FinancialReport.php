<?php

namespace App\Reports\Financial;

use App\Models\Financial\Expense;
use App\Services\Financial\FinancialCalculator;

class FinancialReport
{
    public function __construct(
        private FinancialCalculator $calculator,
    ) {}

    /** @return array<string, mixed> */
    public function generate(int $year): array
    {
        $summary = $this->calculator->calculate($year);

        // expenses.deductible_amount is bigint cents (migration 2026_04_22_230000).
        $deductible = (int) Expense::query()->whereYear('date', $year)->sum('deductible_amount') / 100;

        $monthly = $summary->monthlyBreakdown->map(fn (mixed $m) => [
            'month' => substr($m->monthName, 0, 3),
            'revenue' => $m->revenue,
            'expenses' => $m->expenses,
            'profit' => $m->net,
        ]);

        $expensesByCategory = $summary->expenseBreakdown->map(fn (array $e) => [
            'category' => $e['category'],
            'amount' => (float) $e['amount'],
        ])->values()->all();

        return [
            'totalRevenue' => $summary->totalRevenue,
            'totalExpenses' => $summary->totalExpenses,
            'profit' => $summary->netProfit,
            'deductible' => $deductible,
            'monthly' => $monthly,
            'expensesByCategory' => $expensesByCategory,
        ];
    }
}
