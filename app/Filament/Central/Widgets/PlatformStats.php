<?php

namespace App\Filament\Central\Widgets;

use App\Models\SupportTicket;
use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStats extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $planPrices = ['starter' => 9, 'growth' => 19, 'pro' => 29];
        $activeTenants = Tenant::where('is_active', true)->get();
        $mrr = $activeTenants->sum(fn ($t) => $planPrices[$t->plan] ?? 0);

        $totalTenants = Tenant::count();
        $trialTenants = Tenant::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();

        $openTickets = SupportTicket::where('status', 'open')->count();

        return [
            Stat::make('MRR', '$' . number_format($mrr))
                ->description($activeTenants->count() . ' paying')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Total Bakeries', $totalTenants)
                ->description($activeTenants->count() . ' active')
                ->color('success')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('On Trial', $trialTenants)
                ->description('Free trial')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Open Tickets', $openTickets)
                ->description($openTickets > 0 ? 'Needs attention' : 'All clear')
                ->color($openTickets > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-inbox'),
        ];
    }
}
