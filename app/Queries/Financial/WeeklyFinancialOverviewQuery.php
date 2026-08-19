<?php

namespace App\Queries\Financial;

use App\Models\Financial\Expense;
use App\ValueObjects\DateRange;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class WeeklyFinancialOverviewQuery
{
    /**
     * @return array{datasets: list<array{label: string, data: list<float>, backgroundColor: string}>, labels: list<string>}
     */
    public function get(bool $includeComparison): array
    {
        $range = DateRange::thisWeek();
        $revenueByDay = collect(RevenueQuery::dailyBreakdown($range));
        $expensesByDay = $this->expensesByDay($range);
        $labels = [];
        $revenue = [];
        $expenses = [];

        for ($date = $range->start->copy(); $date->lte($range->end); $date->addDay()) {
            $key = $date->toDateString();
            $labels[] = $date->format('D');
            $revenue[] = round((float) ($revenueByDay[$key] ?? 0), 2);
            $expenses[] = round((float) ($expensesByDay[$key] ?? 0), 2);
        }

        $datasets = [
            ['label' => 'Revenue ($)', 'data' => $revenue, 'backgroundColor' => '#8b5e3c'],
            ['label' => 'Expenses ($)', 'data' => $expenses, 'backgroundColor' => '#dc2626'],
        ];

        if ($includeComparison) {
            $lastRange = new DateRange($range->start->copy()->subWeek(), $range->end->copy()->subWeek());
            $lastRevenueByDay = collect(RevenueQuery::dailyBreakdown($lastRange));
            $lastRevenue = [];

            for ($date = $lastRange->start->copy(); $date->lte($lastRange->end); $date->addDay()) {
                $lastRevenue[] = round((float) ($lastRevenueByDay[$date->toDateString()] ?? 0), 2);
            }

            $datasets[] = [
                'label' => 'Last Week Revenue ($)',
                'data' => $lastRevenue,
                'backgroundColor' => '#d4a574',
            ];
        }

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    /** @return Collection<string, float> */
    private function expensesByDay(DateRange $range): Collection
    {
        $values = Expense::query()
            ->whereBetween('date', $range->toArray())
            ->selectRaw('DATE(date) as day, SUM(amount * business_percentage / 100) as total')
            ->groupBy('day')
            ->pluck('total', 'day');
        $expenses = [];

        foreach ($values as $day => $total) {
            if (is_string($day)) {
                $expenses[$day] = Arr::float(['total' => $total], 'total', 0.0);
            }
        }

        return collect($expenses);
    }
}
