<?php

namespace App\Filament\Central\Widgets;

use App\Models\SupportTicket;
use App\Models\Tenant;
use Carbon\Carbon;
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

        // Sparkline data: last 6 months
        $mrrChart = [];
        $bakeryChart = [];
        $trialChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();

            // MRR for that month
            $activeInMonth = Tenant::where('is_active', true)
                ->where('created_at', '<=', $monthEnd)
                ->get();
            $mrrChart[] = (int) $activeInMonth->sum(fn ($t) => $planPrices[$t->plan] ?? 0);

            // Cumulative bakeries
            $bakeryChart[] = Tenant::where('created_at', '<=', $monthEnd)->count();

            // Trials active at end of that month
            $trialChart[] = Tenant::whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', $monthEnd)
                ->where('created_at', '<=', $monthEnd)
                ->count();
        }

        // Ticket sparkline: last 6 days
        $ticketChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $ticketChart[] = SupportTicket::whereDate('created_at', $day->toDateString())->count();
        }

        return [
            Stat::make('MRR', '$' . number_format($mrr))
                ->description($activeTenants->count() . ' paying')
                ->color('success')
                ->icon('heroicon-o-currency-dollar')
                ->chart($mrrChart)
                ->chartColor('success'),

            Stat::make('Total Bakeries', $totalTenants)
                ->description($activeTenants->count() . ' active')
                ->color('success')
                ->icon('heroicon-o-building-storefront')
                ->chart($bakeryChart)
                ->chartColor('success'),

            Stat::make('On Trial', $trialTenants)
                ->description('Free trial')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->chart($trialChart)
                ->chartColor('warning'),

            Stat::make('Open Tickets', $openTickets)
                ->description($openTickets > 0 ? 'Needs attention' : 'All clear')
                ->color($openTickets > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-inbox')
                ->chart($ticketChart)
                ->chartColor($openTickets > 0 ? 'danger' : 'success'),
        ];
    }
}
