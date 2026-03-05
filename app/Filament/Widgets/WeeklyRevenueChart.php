<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;
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
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();
        $period = CarbonPeriod::create($start, $end);

        $labels = [];
        $revenue = [];
        $expenses = [];

        foreach ($period as $date) {
            $labels[] = $date->format('D');
            $revenue[] = (float) Order::where('status', '!=', 'cancelled')
                ->whereDate('requested_date', $date)
                ->sum('total');
            $expenses[] = (float) Expense::whereDate('date', $date)
                ->get()
                ->sum(fn ($e) => $e->deductible_amount);
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
