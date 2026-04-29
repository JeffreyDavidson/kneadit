<?php

namespace App\Filament\Central\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Platform\Tenant;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class RevenueOverview extends StatsOverviewWidget
{
    use CachesWidgetData;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $data = $this->cached('main', [900, 1800], function (): array {
            /** @var Collection<int, Tenant> $activeTenants */
            $activeTenants = Tenant::query()->where('is_active', true)->get();

            $mrr = $activeTenants->sum(fn (Tenant $tenant) => $tenant->plan->priceInDollars());

            $totalTrialed = Tenant::query()->whereNotNull('trial_ends_at')->count();
            $convertedFromTrial = Tenant::query()->whereNotNull('trial_ends_at')
                ->where('is_active', true)
                ->whereNotNull('plan')
                ->where('trial_ends_at', '<=', now())
                ->count();
            $trialConversion = $totalTrialed > 0
                ? round(($convertedFromTrial / $totalTrialed) * 100, 1)
                : 0;

            $totalEver = Tenant::query()->count();
            $inactive = Tenant::query()->where('is_active', false)->count();
            $churnRate = $totalEver > 0
                ? round(($inactive / $totalEver) * 100, 1)
                : 0;

            $activePayingCount = $activeTenants->count();
            $arpu = $activePayingCount > 0 ? $mrr / $activePayingCount : 0;

            return [
                'mrr' => $mrr,
                'arr' => $mrr * 12,
                'arpu' => $arpu,
                'activePayingCount' => $activePayingCount,
                'totalTrialed' => $totalTrialed,
                'convertedFromTrial' => $convertedFromTrial,
                'trialConversion' => $trialConversion,
                'totalEver' => $totalEver,
                'inactive' => $inactive,
                'churnRate' => $churnRate,
            ];
        });

        return [
            Stat::make('ARPU', Number::currency($data['arpu']))
                ->description('Avg revenue per bakery · ' . $data['activePayingCount'] . ' paying')
                ->color('success')
                ->icon(Heroicon::OutlinedUserCircle),

            Stat::make('Annual Recurring Revenue', Number::currency($data['arr']))
                ->description('MRR × 12')
                ->color('success')
                ->icon(Heroicon::OutlinedBanknotes),

            Stat::make('Trial Conversion', $data['trialConversion'] . '%')
                ->description($data['convertedFromTrial'] . ' of ' . $data['totalTrialed'] . ' converted')
                ->color($data['trialConversion'] >= 50 ? 'success' : 'warning')
                ->icon(Heroicon::OutlinedArrowPath),

            Stat::make('Churn Rate', $data['churnRate'] . '%')
                ->description($data['inactive'] . ' inactive of ' . $data['totalEver'])
                ->color($data['churnRate'] <= 10 ? 'success' : 'danger')
                ->icon(Heroicon::OutlinedArrowTrendingDown),
        ];
    }

    protected function cachePrefix(): string
    {
        return 'revenue_overview';
    }
}
