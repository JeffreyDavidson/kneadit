<?php

namespace App\Filament\Central\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Platform\SupportTicket;
use App\Models\Platform\Tenant;
use App\Queries\Analytics\DateCountQuery;
use App\Queries\Platform\PlatformRevenueMetricsQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PlatformStats extends StatsOverviewWidget
{
    use CachesWidgetData;

    #[\Override]
    protected ?string $pollingInterval = null;

    #[\Override]
    protected static ?int $sort = 0;

    #[\Override]
    protected function getStats(): array
    {
        $data = $this->cached('main', [900, 1800], fn (): array => $this->loadData());

        return [
            Stat::make('MRR', Number::currency($data['mrr']))
                ->description($data['payingCount'].' paying')
                ->color('success')
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->chart($data['mrrChart'])
                ->chartColor('success'),
            Stat::make('Total Bakeries', $data['totalTenants'])
                ->description($data['activeTenants'].' active')
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
     * @return array{mrr: float, payingCount: int, activeTenants: int, totalTenants: int, trialTenants: int, openTickets: int, mrrChart: list<float>, bakeryChart: list<float>, trialChart: list<float>, ticketChart: list<float>}
     */
    private function loadData(): array
    {
        $allTenants = Tenant::query()->select('is_active', 'created_at', 'trial_ends_at')->get();
        $activeTenants = $allTenants->where('is_active', true);
        $revenue = resolve(PlatformRevenueMetricsQuery::class);
        $metrics = $revenue->get();
        $totalTenants = $allTenants->count();
        $trialTenants = $allTenants->filter(fn (Tenant $tenant): bool => $tenant->trial_ends_at !== null && $tenant->trial_ends_at > now())->count();
        $openTickets = SupportTicket::query()->open()->count();
        $monthEnds = [];
        $bakeryChart = [];
        $trialChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthEnds[] = $monthEnd;
            $bakeryChart[] = (float) $allTenants->filter(fn (Tenant $tenant): bool => $tenant->created_at <= $monthEnd)->count();
            $trialChart[] = (float) $allTenants->filter(fn (Tenant $tenant): bool => $tenant->trial_ends_at !== null && $tenant->trial_ends_at > $monthEnd && $tenant->created_at <= $monthEnd)->count();
        }

        $mrrChart = $revenue->monthlyRevenueAt($monthEnds);

        $ticketCounts = DateCountQuery::count(
            SupportTicket::query(),
            'created_at',
            now()->subDays(5),
            now(),
        );
        $ticketChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $ticketChart[] = (float) ($ticketCounts[now()->subDays($i)->format('Y-m-d')] ?? 0);
        }

        return [
            'mrr' => (float) $metrics->mrr,
            'payingCount' => $metrics->payingCount,
            'activeTenants' => $activeTenants->count(),
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
