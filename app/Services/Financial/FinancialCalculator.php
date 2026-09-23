<?php

namespace App\Services\Financial;

use App\DataTransferObjects\Financial\FinancialExpenseBreakdown;
use App\DataTransferObjects\Financial\FinancialSummary;
use App\DataTransferObjects\Financial\MonthlyFinancials;
use App\Models\Financial\Expense;
use App\Models\Financial\Income;
use App\Models\Orders\Order;
use App\ValueObjects\Money;
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

    /** @return array{totalRevenue: Money, totalExpenses: Money, netProfit: Money} */
    private function yearlyTotals(int $year): array
    {
        $orderRevenue = Money::fromCents((int) Order::query()->paidInYear($year)->sum('total'));
        $otherIncome = Money::fromCents((int) Income::query()->forYear($year)->sum('amount'));
        $totalRevenue = $orderRevenue->add($otherIncome);

        $totalExpenses = Money::fromCents((int) Expense::query()->forYear($year)->sum('amount'));

        return [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $totalRevenue->subtract($totalExpenses),
        ];
    }

    /** @return Collection<int, MonthlyFinancials> */
    private function monthlyBreakdown(int $year): Collection
    {
        $orderRevenueByMonth = Order::query()->paidInYear($year)
            ->whereNotNull('delivery_date')
            ->get(['delivery_date', 'total'])
            ->groupBy(fn (Order $o): int => (int) $o->delivery_date?->month)
            ->map(fn (Collection $group): Money => $group->reduce(fn (Money $sum, Order $o): Money => $sum->add($o->total), Money::zero()));

        $incomeByMonth = Income::query()->forYear($year)
            ->whereNotNull('date')
            ->get(['date', 'amount'])
            ->groupBy(fn (Income $i): int => (int) $i->date?->month)
            ->map(fn (Collection $group): Money => $group->reduce(fn (Money $sum, Income $i): Money => $sum->add($i->amount), Money::zero()));

        $expensesByMonth = Expense::query()->forYear($year)
            ->whereNotNull('date')
            ->get(['date', 'amount'])
            ->groupBy(fn (Expense $e): int => (int) $e->date?->month)
            ->map(fn (Collection $group): Money => $group->reduce(fn (Money $sum, Expense $e): Money => $sum->add($e->amount), Money::zero()));

        $breakdown = [];

        for ($month = 1; $month <= 12; $month++) {
            $totalMonthRevenue = ($orderRevenueByMonth[$month] ?? Money::zero())->add($incomeByMonth[$month] ?? Money::zero());
            $monthExpenses = $expensesByMonth[$month] ?? Money::zero();

            $breakdown[] = new MonthlyFinancials(
                month: $month,
                monthName: date('F', (int) mktime(0, 0, 0, $month, 1)),
                revenue: $totalMonthRevenue,
                expenses: $monthExpenses,
                net: $totalMonthRevenue->subtract($monthExpenses),
            );
        }

        return collect($breakdown);
    }

    /** @return Collection<int, FinancialExpenseBreakdown> */
    private function expenseBreakdown(int $year, Money $totalExpenses): Collection
    {
        if ($totalExpenses->isZero()) {
            return new Collection;
        }

        // total_amount comes from SUM(expenses.amount) which is bigint cents.
        return Expense::query()->forYear($year)
            ->byCategory()
            ->get()
            ->map(function (Expense $expense) use ($totalExpenses): FinancialExpenseBreakdown {
                $amount = Money::fromCents((int) $expense->total_amount);

                return new FinancialExpenseBreakdown(
                    category: $expense->category->getLabel(),
                    amount: $amount,
                    percentage: round(($amount->cents() / $totalExpenses->cents()) * 100, 1),
                );
            })
            ->sortByDesc(fn (FinancialExpenseBreakdown $entry): int => $entry->amount->cents());
    }

    /** @return array{cogsAmount: Money, cogsPercentage: float} */
    private function cogs(int $year, Money $totalExpenses): array
    {
        $cogsAmount = Money::fromCents((int) Expense::query()->forYear($year)->cogs()->sum('amount'));

        $cogsPercentage = $totalExpenses->isPositive()
            ? round(($cogsAmount->cents() / $totalExpenses->cents()) * 100, 1)
            : 0.0;

        return [
            'cogsAmount' => $cogsAmount,
            'cogsPercentage' => $cogsPercentage,
        ];
    }
}
