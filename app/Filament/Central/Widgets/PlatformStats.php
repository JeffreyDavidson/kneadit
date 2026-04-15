<?php

namespace App\Filament\Central\Widgets;

use App\Enums\Platform\SupportTicketStatus;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class PlatformStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $activeTenants = Tenant::query()->where('is_active', true)->get();
        $mrr = $activeTenants->sum(fn (Tenant $t) => $t->plan?->priceInDollars() ?? 0);

        $totalTenants = Tenant::query()->count();
        $trialTenants = Tenant::query()->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();

        $openTickets = SupportTicket::query()->where('status', SupportTicketStatus::Open)->count();

        // Sparkline data: last 6 months (1 query + in-memory filtering)
        /** @var Collection<int, Tenant> $allTenants */
        $allTenants = Tenant::query()->select('plan', 'is_active', 'created_at', 'trial_ends_at')->get();
        $mrrChart = [];
        $bakeryChart = [];
        $trialChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $activeInMonth = $allTenants->filter(fn (Tenant $t) => $t->is_active && $t->created_at <= $monthEnd);
            $mrrChart[] = (int) $activeInMonth->sum(fn (Tenant $t) => $t->plan?->priceInDollars() ?? 0);
            $bakeryChart[] = $allTenants->filter(fn (Tenant $t) => $t->created_at <= $monthEnd)->count();
            $trialChart[] = $allTenants->filter(fn (Tenant $t) => $t->trial_ends_at && $t->trial_ends_at > $monthEnd && $t->created_at <= $monthEnd)->count();
        }

        // Ticket sparkline: last 6 days (1 grouped query)
        $sixDaysAgo = now()->subDays(5)->startOfDay();
        $ticketCounts = SupportTicket::query()
            ->where('created_at', '>=', $sixDaysAgo)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $ticketChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $ticketChart[] = (int) ($ticketCounts[$day] ?? 0);
        }

        return [
            Stat::make('MRR', Number::currency($mrr))
                ->description($activeTenants->count() . ' paying')
                ->color('success')
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->chart($mrrChart)
                ->chartColor('success'),

            Stat::make('Total Bakeries', $totalTenants)
                ->description($activeTenants->count() . ' active')
                ->color('success')
                ->icon(Heroicon::OutlinedBuildingStorefront)
                ->chart($bakeryChart)
                ->chartColor('success'),

            Stat::make('On Trial', $trialTenants)
                ->description('Free trial')
                ->color('warning')
                ->icon(Heroicon::OutlinedClock)
                ->chart($trialChart)
                ->chartColor('warning'),

            Stat::make('Open Tickets', $openTickets)
                ->description($openTickets > 0 ? 'Needs attention' : 'All clear')
                ->color($openTickets > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedInbox)
                ->chart($ticketChart)
                ->chartColor($openTickets > 0 ? 'danger' : 'success'),
        ];
    }
}
