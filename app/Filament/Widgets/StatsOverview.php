<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Queries\Dashboard\StatsOverviewQuery;
use Filament\Widgets\Widget;
use Illuminate\Support\Number;

class StatsOverview extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 1;

    protected string $view = 'filament.widgets.stats-overview';

    /** @return array<int, array<string, mixed>> */
    public function getCards(): array
    {
        $data = $this->cached('main', [300, 600], fn (): array => $this->loadData());

        $ordersDelta = $this->percentChange((float) $data['todaysOrders'], (float) $data['weekAvgOrders']);
        $revenueDelta = $this->percentChange((float) $data['thisWeekRevenue'], (float) $data['lastWeekRevenue']);
        $viewsDelta = $this->percentChange((float) $data['viewsToday'], array_sum($data['viewsChart']) / 7);

        return [
            [
                'label' => "Today's Orders",
                'value' => (string) $data['todaysOrders'],
                'delta' => $this->describeDelta($ordersDelta, 'vs 7-day avg'),
                'tone' => $this->trendTone($ordersDelta),
                'chart' => $this->normaliseChart($data['ordersChart']),
            ],
            [
                'label' => 'Pending Orders',
                'value' => (string) $data['pendingOrders'],
                'delta' => $data['pendingOrders'] > 0 ? 'Awaiting confirmation' : 'All clear',
                'tone' => $this->backlogTone($data['pendingOrders']),
                'chart' => $this->normaliseChart($data['pendingChart']),
            ],
            [
                'label' => "This Week's Revenue",
                'value' => (string) Number::currency($data['thisWeekRevenue']),
                'delta' => $this->describeDelta($revenueDelta, 'vs last week'),
                'tone' => $this->trendTone($revenueDelta),
                'chart' => $this->normaliseChart($data['revenueChart']),
            ],
            [
                'label' => 'Storefront Views Today',
                'value' => (string) Number::format($data['viewsToday']),
                'delta' => $this->describeDelta($viewsDelta, 'vs 7-day avg'),
                'tone' => $this->trendTone($viewsDelta),
                'chart' => $this->normaliseChart($data['viewsChart']),
            ],
        ];
    }

    protected function cachePrefix(): string
    {
        return 'stats_overview';
    }

    /**
     * @return array{
     *     todaysOrders: int,
     *     ordersChart: list<int>,
     *     weekAvgOrders: float|int,
     *     pendingOrders: int,
     *     pendingChart: list<int>,
     *     thisWeekRevenue: float,
     *     lastWeekRevenue: float,
     *     revenueChart: list<int>,
     *     viewsToday: int,
     *     viewsChart: list<int>
     * }
     */
    private function loadData(): array
    {
        return resolve(StatsOverviewQuery::class)->get();
    }

    private function percentChange(float $current, float $baseline): int
    {
        if ($baseline <= 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round(($current - $baseline) / $baseline * 100);
    }

    private function describeDelta(int $delta, string $suffix): string
    {
        $arrow = $delta >= 0 ? '↑' : '↓';

        return "{$arrow} " . abs($delta) . "% {$suffix}";
    }

    private function trendTone(int $delta): string
    {
        return $delta >= 0 ? '#6b9e3a' : '#e8b04a';
    }

    private function backlogTone(int $count): string
    {
        return match (true) {
            $count > 10 => '#d4574a',
            $count > 5 => '#e8b04a',
            default => '#6b9e3a',
        };
    }

    /**
     * @param array<int, int> $values
     * @return array<int, int>
     */
    private function normaliseChart(array $values): array
    {
        if ($values === []) {
            return [];
        }

        $max = max($values) ?: 1;

        return array_map(fn (int $v): int => (int) round($v / $max * 100), $values);
    }
}
