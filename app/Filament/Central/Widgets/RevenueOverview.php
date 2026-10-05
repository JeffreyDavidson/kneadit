<?php

namespace App\Filament\Central\Widgets;

use App\DataTransferObjects\Platform\PlatformRevenueMetrics;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Queries\Platform\PlatformRevenueMetricsQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class RevenueOverview extends StatsOverviewWidget
{
    use CachesWidgetData;

    #[\Override]
    protected ?string $pollingInterval = null;

    #[\Override]
    protected static ?int $sort = 1;

    #[\Override]
    protected function getStats(): array
    {
        $data = $this->cached('main', [900, 1800], fn (): PlatformRevenueMetrics => resolve(PlatformRevenueMetricsQuery::class)->get());

        return [
            Stat::make('ARPU', Number::currency($data->arpu))
                ->description('Avg revenue per bakery · '.$data->payingCount.' paying')
                ->color('success')
                ->icon(Heroicon::OutlinedUserCircle),

            Stat::make('Annual Recurring Revenue', Number::currency($data->mrr * 12))
                ->description('MRR × 12')
                ->color('success')
                ->icon(Heroicon::OutlinedBanknotes),

            Stat::make('Trial Conversion', $data->trialConversion.'%')
                ->description($data->convertedCount.' of '.$data->trialedCount.' finished trials converted')
                ->color($data->trialConversion >= 50 ? 'success' : 'warning')
                ->icon(Heroicon::OutlinedArrowPath),

            Stat::make('Churn Rate', $data->churnRate.'%')
                ->description($data->churnedCount.' churned in the last 30 days')
                ->color($data->churnRate <= 10 ? 'success' : 'danger')
                ->icon(Heroicon::OutlinedArrowTrendingDown),
        ];
    }

    protected function cachePrefix(): string
    {
        return 'revenue_overview';
    }
}
