<?php

namespace App\Filament\Central\Widgets;

use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected static ?int $sort = 0;
    protected function getStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $trialTenants = Tenant::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();

        $planCounts = Tenant::where('is_active', true)
            ->selectRaw('plan, count(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();

        return [
            Stat::make('Total Bakeries', $totalTenants)
                ->description($activeTenants . ' active')
                ->color('success')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('On Trial', $trialTenants)
                ->description('Free trial period')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Starter Plan', $planCounts['starter'] ?? 0)
                ->description('$9/mo')
                ->color('gray')
                ->icon('heroicon-o-cube'),

            Stat::make('Growth Plan', $planCounts['growth'] ?? 0)
                ->description('$19/mo')
                ->color('info')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('Pro Plan', $planCounts['pro'] ?? 0)
                ->description('$29/mo')
                ->color('success')
                ->icon('heroicon-o-star'),
        ];
    }
}
