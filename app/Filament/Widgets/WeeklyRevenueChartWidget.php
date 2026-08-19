<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Financial\WeeklyFinancialOverviewQuery;
use Filament\Widgets\ChartWidget;

class WeeklyRevenueChartWidget extends ChartWidget
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

        return $this->cached($cacheKey, [300, 600], fn (): array => resolve(WeeklyFinancialOverviewQuery::class)->get($this->isSize('lg')));
    }

    protected function cachePrefix(): string
    {
        return 'weekly_revenue';
    }
}
