<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Financial\Expense;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class WeeklyRevenueChartWidget extends ChartWidget
{
    use CachesWidgetData;
    use HasDashboardSize;

    #[\Override]
    protected static ?int $sort = 5;

    #[\Override]
    protected ?string $heading = 'Weekly Financial Overview';

    // Override Filament's default chart view so the chart renders inside our
    // <x-tenant-admin.dashboard.preview-card> shell instead of <x-filament::section>.
    #[\Override]
    protected string $view = 'filament.widgets.weekly-revenue';

    protected function getType(): string
    {
        return 'bar';
    }

    #[\Override]
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
                $revenue[] = ($revenueByDay[$key] ?? Money::zero())->dollars();
                $expenses[] = ($expensesByDay[$key] ?? Money::zero())->dollars();
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
                    $lastRevenue[] = ($lastRevenueByDay[$date->format('Y-m-d')] ?? Money::zero())->dollars();
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

    /**
     * Business-portion expenses per day. deductible_amount is the integer cents
     * the ExpenseObserver already computed per row (amount x business_percentage,
     * rounded), so summing it avoids redoing the percentage math in SQL.
     *
     * @return Collection<string, Money>
     */
    private function expensesByDay(DateRange $range): Collection
    {
        return Expense::query()
            ->whereBetween('date', $range->toArray())
            ->selectRaw('DATE(date) as day, SUM(deductible_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->mapWithKeys(fn (mixed $total, mixed $day): array => [
                Arr::string(['day' => $day], 'day') => Money::fromCents(is_numeric($total) ? (int) $total : 0),
            ]);
    }

    protected function cachePrefix(): string
    {
        return 'weekly_revenue';
    }
}
