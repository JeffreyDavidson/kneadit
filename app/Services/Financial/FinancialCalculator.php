<?php

namespace App\Services\Financial;

use App\DataTransferObjects\Financial\FinancialSummary;
use App\DataTransferObjects\Financial\MonthlyFinancials;
use App\Models\Financial\Expense;
use App\Models\Financial\Income;
use App\Models\Orders\Order;
use Illuminate\Support\Collection;

class FinancialCalculator
{
    public function calculate(int $year): FinancialSummary
    {
        $totals = $this->yearlyTotals($year);
        $monthlyBreakdown = $this->monthlyBreakdown($year);
        $expenseBreakdown = $this->expenseBreakdown($year, $totals['totalExpenses']);
        $cogs = $this->cogs($year, $totals['totalExpenses']);

        return new FinancialSummary(
            totalRevenue: $totals['totalRevenue'],
            totalExpenses: $totals['totalExpenses'],
            netProfit: $totals['netProfit'],
            cogsAmount: $cogs['cogsAmount'],
            cogsPercentage: $cogs['cogsPercentage'],
            monthlyBreakdown: $monthlyBreakdown,
            expenseBreakdown: $expenseBreakdown,
        );
    }

    /** @return array{totalRevenue: float, totalExpenses: float, netProfit: float} */
    private function yearlyTotals(int $year): array
    {
        $orderRevenue = (float) Order::query()->paidInYear($year)->sum('total');
        $otherIncome = (float) Income::query()->forYear($year)->sum('amount');
        $totalRevenue = $orderRevenue + $otherIncome;

        $totalExpenses = (float) Expense::query()->forYear($year)->sum('amount');

        return [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $totalRevenue - $totalExpenses,
        ];
    }

    /** @return Collection<int, MonthlyFinancials> */
    private function monthlyBreakdown(int $year): Collection
    {
        $orderRevenueByMonth = Order::query()->paidInYear($year)
            ->whereNotNull('delivery_date')
            ->get(['delivery_date', 'total'])
            ->groupBy(fn (Order $o) => (int) $o->delivery_date?->month)
            ->map(fn (Collection $group) => $group->sum(fn (Order $o) => $o->total->dollars()));

        $incomeByMonth = Income::query()->forYear($year)
            ->whereNotNull('date')
            ->get(['date', 'amount'])
            ->groupBy(fn (Income $i) => (int) $i->date?->month)
            ->map(fn (Collection $group) => $group->sum(fn (Income $i) => $i->amount->dollars()));

        $expensesByMonth = Expense::query()->forYear($year)
            ->whereNotNull('date')
            ->get(['date', 'amount'])
            ->groupBy(fn (Expense $e) => (int) $e->date?->month)
            ->map(fn (Collection $group) => $group->sum(fn (Expense $e) => $e->amount->dollars()));

        $breakdown = collect();

        for ($month = 1; $month <= 12; $month++) {
            $totalMonthRevenue = ($orderRevenueByMonth[$month] ?? 0.0) + ($incomeByMonth[$month] ?? 0.0);
            $monthExpenses = $expensesByMonth[$month] ?? 0.0;

            $breakdown->push(new MonthlyFinancials(
                month: $month,
                monthName: date('F', (int) mktime(0, 0, 0, $month, 1)),
                revenue: $totalMonthRevenue,
                expenses: $monthExpenses,
                net: $totalMonthRevenue - $monthExpenses,
            ));
        }

        return $breakdown;
    }

    /** @return Collection<int, array{category: string, amount: float|null, percentage: float}> */
    private function expenseBreakdown(int $year, float $totalExpenses): Collection
    {
        if ($totalExpenses == 0) {
            return collect();
        }

        return Expense::query()->forYear($year)
            ->byCategory()
            ->get()
            ->map(fn (Expense $expense) => [
                'category' => $expense->category->getLabel(),
                'amount' => $expense->total_amount,
                'percentage' => round(($expense->total_amount / $totalExpenses) * 100, 1),
            ])
            ->sortByDesc('amount');
    }

    /** @return array{cogsAmount: float, cogsPercentage: float} */
    private function cogs(int $year, float $totalExpenses): array
    {
        $cogsAmount = (float) Expense::query()->forYear($year)->cogs()->sum('amount');

        $cogsPercentage = $totalExpenses > 0
            ? round(($cogsAmount / $totalExpenses) * 100, 1)
            : 0.0;

        return [
            'cogsAmount' => $cogsAmount,
            'cogsPercentage' => $cogsPercentage,
        ];
    }
}
