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

        $deductible = (float) Expense::query()->whereYear('date', $year)->sum('deductible_amount');

        $monthly = $summary->monthlyBreakdown->map(fn ($m) => [
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
