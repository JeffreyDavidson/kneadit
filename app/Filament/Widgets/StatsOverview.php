<?php

namespace App\Filament\Widgets;

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Models\Engagement\PageView;
use App\Models\Orders\Order;
use App\Queries\Financial\RevenueQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    use CachesWidgetData;

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    /** @return array<int, Stat> */
    protected function getStats(): array
    {
        $data = $this->cached('main', [300, 600], function (): array {
            $today = Date::today();
            $weekStart = Date::now()->startOfWeek();
            $weekEnd = Date::now()->endOfWeek();
            $lastWeekStart = $weekStart->copy()->subWeek();
            $lastWeekEnd = $weekEnd->copy()->subWeek();

            // 7-day chart series (oldest → newest, today is last index).
            $ordersChart = [];
            $revenueChart = [];
            $viewsChart = [];
            $pendingCreatedChart = [];

            for ($i = 6; $i >= 0; $i--) {
                $d = $today->copy()->subDays($i);
                $startOfDay = $d->copy()->startOfDay();
                $endOfDay = $d->copy()->endOfDay();

                $ordersChart[] = Order::query()->whereDate('delivery_date', $d)->count();
                $revenueChart[] = (int) RevenueQuery::total([$d->toDateString(), $d->toDateString()]);
                $viewsChart[] = PageView::query()->whereNull('product_id')
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->count();
                $pendingCreatedChart[] = Order::query()
                    ->where('status', OrderStatus::Pending)
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->count();
            }

            return [
                'todaysOrders' => $ordersChart[6] ?? 0,
                'ordersChart' => $ordersChart,
                'weekAvgOrders' => array_sum($ordersChart) / 7,
                'pendingOrders' => Order::query()->where('status', OrderStatus::Pending)->count(),
                'pendingChart' => $pendingCreatedChart,
                'thisWeekRevenue' => RevenueQuery::total([$weekStart->toDateString(), $weekEnd->toDateString()]),
                'lastWeekRevenue' => RevenueQuery::total([$lastWeekStart->toDateString(), $lastWeekEnd->toDateString()]),
                'revenueChart' => $revenueChart,
                'viewsToday' => $viewsChart[6] ?? 0,
                'viewsChart' => $viewsChart,
            ];
        });

        $ordersDelta = $this->percentChange($data['todaysOrders'], $data['weekAvgOrders']);
        $revenueDelta = $this->percentChange($data['thisWeekRevenue'], $data['lastWeekRevenue']);
        $viewsDelta = $this->percentChange($data['viewsToday'], array_sum($data['viewsChart']) / 7);

        return [
            Stat::make("Today's Orders", $data['todaysOrders'])
                ->description($this->describeDelta($ordersDelta, 'vs 7-day avg'))
                ->descriptionIcon($this->trendIcon($ordersDelta))
                ->color($this->trendColor($ordersDelta))
                ->icon(Heroicon::OutlinedShoppingBag)
                ->chart($data['ordersChart'])
                ->chartColor($this->trendColor($ordersDelta)),

            Stat::make('Pending Orders', $data['pendingOrders'])
                ->description($data['pendingOrders'] > 0 ? 'Awaiting confirmation' : 'All clear')
                ->color($this->backlogColor($data['pendingOrders']))
                ->icon(Heroicon::OutlinedClock)
                ->chart($data['pendingChart'])
                ->chartColor($this->backlogColor($data['pendingOrders'])),

            Stat::make("This Week's Revenue", Number::currency($data['thisWeekRevenue']))
                ->description($this->describeDelta($revenueDelta, 'vs last week'))
                ->descriptionIcon($this->trendIcon($revenueDelta))
                ->color($this->trendColor($revenueDelta))
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->chart($data['revenueChart'])
                ->chartColor($this->trendColor($revenueDelta)),

            Stat::make('Storefront Views Today', Number::format($data['viewsToday']))
                ->description($this->describeDelta($viewsDelta, 'vs 7-day avg'))
                ->descriptionIcon($this->trendIcon($viewsDelta))
                ->color($this->trendColor($viewsDelta))
                ->icon(Heroicon::OutlinedEye)
                ->chart($data['viewsChart'])
                ->chartColor($this->trendColor($viewsDelta)),
        ];
    }

    protected function cachePrefix(): string
    {
        return 'stats_overview';
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
        $direction = $delta >= 0 ? 'above' : 'below';

        return abs($delta) . "% {$direction} {$suffix}";
    }

    private function trendIcon(int $delta): Heroicon
    {
        return $delta >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown;
    }

    private function trendColor(int $delta): string
    {
        return $delta >= 0 ? 'success' : 'warning';
    }

    private function backlogColor(int $count): string
    {
        return match (true) {
            $count > 10 => 'danger',
            $count > 5 => 'warning',
            default => 'success',
        };
    }
}
