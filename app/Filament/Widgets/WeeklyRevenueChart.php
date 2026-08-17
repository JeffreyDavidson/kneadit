<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Financial\Expense;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use Filament\Widgets\ChartWidget;

class WeeklyRevenueChart extends ChartWidget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 5;

    protected ?string $heading = 'Weekly Financial Overview';

    // Override Filament's default chart view so the chart renders inside our
    // <x-admin.dashboard.preview-card> shell instead of <x-filament::section>.
    protected string $view = 'filament.widgets.weekly-revenue';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $cacheKey = $this->isSize('lg') ? 'main_compare' : 'main';

        return $this->cached($cacheKey, [300, 600], function (): array {
            $range = DateRange::thisWeek();
            $revenueByDay = collect(RevenueQuery::dailyBreakdown($range));
            $expensesByDay = $this->expensesByDay($range);

            $labels = [];
            $revenue = [];
            $expenses = [];

            for ($date = $range->start->copy(); $date->lte($range->end); $date->addDay()) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('D');
                $revenue[] = round((float) ($revenueByDay[$key] ?? 0), 2);
                $expenses[] = round((float) ($expensesByDay[$key] ?? 0), 2);
            }

            $datasets = [
                ['label' => 'Revenue ($)', 'data' => $revenue, 'backgroundColor' => '#8b5e3c'],
                ['label' => 'Expenses ($)', 'data' => $expenses, 'backgroundColor' => '#dc2626'],
            ];

            // lg widens the lens with last week's comparison overlay so the
            // baker can see week-over-week trend at a glance.
            if ($this->isSize('lg')) {
                $lastRange = new DateRange(
                    $range->start->copy()->subWeek(),
                    $range->end->copy()->subWeek(),
                );
                $lastRevenueByDay = collect(RevenueQuery::dailyBreakdown($lastRange));
                $lastRevenue = [];
                for ($date = $lastRange->start->copy(); $date->lte($lastRange->end); $date->addDay()) {
                    $lastRevenue[] = round((float) ($lastRevenueByDay[$date->format('Y-m-d')] ?? 0), 2);
                }
                $datasets[] = [
                    'label' => 'Last Week Revenue ($)',
                    'data' => $lastRevenue,
                    'backgroundColor' => '#d4a574',
                ];
            }

            return ['datasets' => $datasets, 'labels' => $labels];
        });
    }

    /** @return \Illuminate\Support\Collection<string, float> */
    private function expensesByDay(DateRange $range): \Illuminate\Support\Collection
    {
        $values = Expense::query()
            ->whereBetween('date', $range->toArray())
            ->selectRaw('DATE(date) as day, SUM(amount * business_percentage / 100) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $expenses = [];

        foreach ($values as $day => $total) {
            if (is_string($day)) {
                $expenses[$day] = (float) $total;
            }
        }

        return collect($expenses);
    }

    protected function cachePrefix(): string
    {
        return 'weekly_revenue';
    }
}
