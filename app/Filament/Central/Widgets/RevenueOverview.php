<?php

namespace App\Filament\Central\Widgets;

use App\Models\Platform\Tenant;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class RevenueOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $planPrices = [
            'starter' => 9,
            'growth' => 19,
            'pro' => 29,
        ];

        $activeTenants = Tenant::query()->where('is_active', true)->get();

        $mrr = $activeTenants->sum(function (Tenant $tenant) use ($planPrices) {
            return $planPrices[$tenant->plan] ?? 0;
        });

        $arr = $mrr * 12;

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

        return [
            Stat::make('Monthly Recurring Revenue', Number::currency($mrr))
                ->description('Active subscriptions')
                ->color('success')
                ->icon(Heroicon::OutlinedCurrencyDollar),

            Stat::make('Annual Recurring Revenue', Number::currency($arr))
                ->description('MRR × 12')
                ->color('success')
                ->icon(Heroicon::OutlinedBanknotes),

            Stat::make('Trial Conversion', $trialConversion . '%')
                ->description($convertedFromTrial . ' of ' . $totalTrialed . ' converted')
                ->color($trialConversion >= 50 ? 'success' : 'warning')
                ->icon(Heroicon::OutlinedArrowPath),

            Stat::make('Churn Rate', $churnRate . '%')
                ->description($inactive . ' inactive of ' . $totalEver)
                ->color($churnRate <= 10 ? 'success' : 'danger')
                ->icon(Heroicon::OutlinedArrowTrendingDown),
        ];
    }
}
