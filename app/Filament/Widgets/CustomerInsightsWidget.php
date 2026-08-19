<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Customers\CustomerInsightsQuery;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class CustomerInsightsWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 8;

    protected string $view = 'filament.widgets.customer-insights';

    public function getNewCustomersThisWeek(): int
    {
        $weekKey = Date::now()->startOfWeek()->format('Y-W');

        return $this->cached("new_{$weekKey}", [900, 1800], fn (): int => $this->query()->newCustomersThisWeek());
    }

    public function getRepeatCustomerRate(): float
    {
        return $this->cached('repeat', [3600, 7200], fn (): float => $this->query()->repeatCustomerRate());
    }

    /** @return array<string, mixed> */
    public function getAvgOrderValue(): array
    {
        $monthKey = now()->format('Y-m');

        return $this->cached("aov_{$monthKey}", [900, 1800], fn (): array => $this->query()->averageOrderValue());
    }

    protected function cachePrefix(): string
    {
        return 'customer_insights';
    }

    private function query(): CustomerInsightsQuery
    {
        return resolve(CustomerInsightsQuery::class);
    }
}
