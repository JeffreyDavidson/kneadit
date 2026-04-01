<?php

namespace App\Filament\Widgets;

use App\Models\Financial\Expense;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class WeeklyRevenueChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Weekly Financial Overview';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $range = DateRange::thisWeek();
        $period = CarbonPeriod::create($range->start, $range->end);

        $revenueByDay = collect(RevenueQuery::dailyBreakdown($range));

        $expensesByDay = Expense::query()
            ->whereBetween('date', $range->toArray())
            ->selectRaw('DATE(date) as day, SUM(amount * business_percentage / 100) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $revenue = [];
        $expenses = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('D');
            $revenue[] = round((float) ($revenueByDay[$key] ?? 0), 2);
            $expenses[] = round((float) ($expensesByDay[$key] ?? 0), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => $revenue,
                    'backgroundColor' => '#8b5e3c',
                ],
                [
                    'label' => 'Expenses ($)',
                    'data' => $expenses,
                    'backgroundColor' => '#dc2626',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
