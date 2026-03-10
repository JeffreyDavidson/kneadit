<?php

namespace App\Filament\Central\Widgets;

use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $planPrices = [
            'starter' => 9,
            'growth' => 19,
            'pro' => 29,
        ];

        $activeTenants = Tenant::where('is_active', true)->get();

        $mrr = $activeTenants->sum(function ($tenant) use ($planPrices) {
            return $planPrices[$tenant->plan] ?? 0;
        });

        $arr = $mrr * 12;

        $totalTrialed = Tenant::whereNotNull('trial_ends_at')->count();
        $convertedFromTrial = Tenant::whereNotNull('trial_ends_at')
            ->where('is_active', true)
            ->whereNotNull('plan')
            ->where('trial_ends_at', '<=', now())
            ->count();
        $trialConversion = $totalTrialed > 0
            ? round(($convertedFromTrial / $totalTrialed) * 100, 1)
            : 0;

        $totalEver = Tenant::count();
        $inactive = Tenant::where('is_active', false)->count();
        $churnRate = $totalEver > 0
            ? round(($inactive / $totalEver) * 100, 1)
            : 0;

        return [
            Stat::make('Monthly Recurring Revenue', '$' . number_format($mrr))
                ->description('Active subscriptions')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Annual Recurring Revenue', '$' . number_format($arr))
                ->description('MRR × 12')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Trial Conversion', $trialConversion . '%')
                ->description($convertedFromTrial . ' of ' . $totalTrialed . ' converted')
                ->color($trialConversion >= 50 ? 'success' : 'warning')
                ->icon('heroicon-o-arrow-path'),

            Stat::make('Churn Rate', $churnRate . '%')
                ->description($inactive . ' inactive of ' . $totalEver)
                ->color($churnRate <= 10 ? 'success' : 'danger')
                ->icon('heroicon-o-arrow-trending-down'),
        ];
    }
}
