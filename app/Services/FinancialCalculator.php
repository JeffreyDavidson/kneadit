<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialCalculator
{
    /**
     * Calculate all financial metrics for a given year.
     *
     * @return array<string, mixed>
     */
    public function calculate(int $year): array
    {
        $totals = $this->yearlyTotals($year);
        $monthlyBreakdown = $this->monthlyBreakdown($year);
        $expenseBreakdown = $this->expenseBreakdown($year, $totals['totalExpenses']);
        $cogs = $this->cogs($year, $totals['totalExpenses']);

        return [
            ...$totals,
            'monthlyBreakdown' => $monthlyBreakdown,
            'expenseBreakdown' => $expenseBreakdown,
            ...$cogs,
        ];
    }

    /** @return array{totalRevenue: float, totalExpenses: float, netProfit: float} */
    private function yearlyTotals(int $year): array
    {
        $orderRevenue = (float) Order::query()->whereYear('delivery_date', $year)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('total');

        $otherIncome = (float) Income::query()->whereYear('date', $year)->sum('amount');
        $totalRevenue = $orderRevenue + $otherIncome;

        $totalExpenses = (float) Expense::query()->whereYear('date', $year)->sum('amount');

        return [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $totalRevenue - $totalExpenses,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    private function monthlyBreakdown(int $year): Collection
    {
        $breakdown = collect();

        for ($month = 1; $month <= 12; $month++) {
            $monthRevenue = (float) Order::query()->whereYear('delivery_date', $year)
                ->whereMonth('delivery_date', $month)
                ->where('payment_status', PaymentStatus::Paid)
                ->sum('total');

            $monthIncome = (float) Income::query()->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $monthExpenses = (float) Expense::query()->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $totalMonthRevenue = $monthRevenue + $monthIncome;

            $breakdown->push([
                'month' => $month,
                'month_name' => date('F', (int) mktime(0, 0, 0, $month, 1)),
                'revenue' => $totalMonthRevenue,
                'expenses' => $monthExpenses,
                'net' => $totalMonthRevenue - $monthExpenses,
            ]);
        }

        return $breakdown;
    }

    /** @return Collection<int, array{category: string, amount: float|null, percentage: float}> */
    private function expenseBreakdown(int $year, float $totalExpenses): Collection
    {
        if ($totalExpenses == 0) {
            return collect();
        }

        return Expense::query()->whereYear('date', $year)
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->get()
            ->map(fn (Expense $expense) => [
                'category' => Expense::CATEGORIES[$expense->category],
                'amount' => $expense->total_amount,
                'percentage' => round(($expense->total_amount / $totalExpenses) * 100, 1),
            ])
            ->sortByDesc('amount');
    }

    /** @return array{cogsAmount: float, cogsPercentage: float} */
    private function cogs(int $year, float $totalExpenses): array
    {
        $cogsAmount = (float) Expense::query()->whereYear('date', $year)
            ->whereIn('category', ['ingredients', 'packaging'])
            ->sum('amount');

        $cogsPercentage = $totalExpenses > 0
            ? round(($cogsAmount / $totalExpenses) * 100, 1)
            : 0.0;

        return [
            'cogsAmount' => $cogsAmount,
            'cogsPercentage' => $cogsPercentage,
        ];
    }
}
