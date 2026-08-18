<?php

namespace App\Filament\Central\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PlatformStats extends StatsOverviewWidget
{
    use CachesWidgetData;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $data = $this->cached('main', [900, 1800], fn (): array => $this->loadData());

        return [
            Stat::make('MRR', Number::currency($data['mrr']))
                ->description($data['activePaying'] . ' paying')
                ->color('success')
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->chart($data['mrrChart'])
                ->chartColor('success'),
            Stat::make('Total Bakeries', $data['totalTenants'])
                ->description($data['activePaying'] . ' active')
                ->color('success')
                ->icon(Heroicon::OutlinedBuildingStorefront)
                ->chart($data['bakeryChart'])
                ->chartColor('success'),
            Stat::make('On Trial', $data['trialTenants'])
                ->description('Free trial')
                ->color('warning')
                ->icon(Heroicon::OutlinedClock)
                ->chart($data['trialChart'])
                ->chartColor('warning'),
            Stat::make('Open Tickets', $data['openTickets'])
                ->description($data['openTickets'] > 0 ? 'Needs attention' : 'All clear')
                ->color($data['openTickets'] > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedInbox)
                ->chart($data['ticketChart'])
                ->chartColor($data['openTickets'] > 0 ? 'danger' : 'success'),
        ];
    }

    /**
     * @return array{mrr: float, activePaying: int, totalTenants: int, trialTenants: int, openTickets: int, mrrChart: list<float>, bakeryChart: list<float>, trialChart: list<float>, ticketChart: list<float>}
     */
    private function loadData(): array
    {
        $activeTenants = Tenant::query()->where('is_active', true)->get();
        $mrr = (float) $activeTenants->sum(fn (Tenant $tenant): int => $tenant->plan->priceInDollars());
        $totalTenants = Tenant::query()->count();
        $trialTenants = Tenant::query()->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())->count();
        $openTickets = SupportTicket::query()->open()->count();
        $allTenants = Tenant::query()->select('plan', 'is_active', 'created_at', 'trial_ends_at')->get();
        $mrrChart = [];
        $bakeryChart = [];
        $trialChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $activeInMonth = $allTenants->filter(fn (Tenant $tenant): bool => $tenant->is_active && $tenant->created_at <= $monthEnd);
            $mrrChart[] = (float) $activeInMonth->sum(fn (Tenant $tenant): int => $tenant->plan->priceInDollars());
            $bakeryChart[] = (float) $allTenants->filter(fn (Tenant $tenant): bool => $tenant->created_at <= $monthEnd)->count();
            $trialChart[] = (float) $allTenants->filter(fn (Tenant $tenant): bool => $tenant->trial_ends_at !== null && $tenant->trial_ends_at > $monthEnd && $tenant->created_at <= $monthEnd)->count();
        }

        $ticketCounts = SupportTicket::query()
            ->where('created_at', '>=', now()->subDays(5)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');
        $ticketChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $ticketChart[] = (float) ($ticketCounts[now()->subDays($i)->format('Y-m-d')] ?? 0);
        }

        return [
            'mrr' => $mrr,
            'activePaying' => $activeTenants->count(),
            'totalTenants' => $totalTenants,
            'trialTenants' => $trialTenants,
            'openTickets' => $openTickets,
            'mrrChart' => $mrrChart,
            'bakeryChart' => $bakeryChart,
            'trialChart' => $trialChart,
            'ticketChart' => $ticketChart,
        ];
    }

    protected function cachePrefix(): string
    {
        return 'platform_stats';
    }
}
