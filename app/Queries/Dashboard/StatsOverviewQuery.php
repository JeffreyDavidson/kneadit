<?php

namespace App\Queries\Dashboard;

use App\Enums\Orders\OrderStatus;
use App\Models\Engagement\PageView;
use App\Models\Orders\Order;
use App\Queries\Financial\RevenueQuery;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class StatsOverviewQuery
{
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
    public function get(): array
    {
        $today = Date::today();
        $chartStart = $today->copy()->subDays(6);
        $weekStart = Date::now()->startOfWeek();
        $weekEnd = Date::now()->endOfWeek();
        $lastWeekStart = $weekStart->copy()->subWeek();
        $lastWeekEnd = $weekEnd->copy()->subWeek();

        $dates = $this->dateKeys($chartStart, $today);
        $ordersByDate = $this->ordersByDeliveryDate($chartStart, $today);
        $pendingByDate = $this->pendingOrdersByCreatedDate($chartStart, $today);
        $viewsByDate = $this->storefrontViewsByDate($chartStart, $today);
        $revenueByDate = RevenueQuery::dailyBreakdown([
            $lastWeekStart->toDateString(),
            $weekEnd->toDateString(),
        ]);

        $ordersChart = $this->integerChart($dates, $ordersByDate);
        $pendingChart = $this->integerChart($dates, $pendingByDate);
        $viewsChart = $this->integerChart($dates, $viewsByDate);
        $revenueChart = array_map(
            fn (string $date): int => (int) ($revenueByDate[$date] ?? 0),
            $dates,
        );

        return [
            'todaysOrders' => $ordersChart[6] ?? 0,
            'ordersChart' => $ordersChart,
            'weekAvgOrders' => array_sum($ordersChart) / 7,
            'pendingOrders' => Order::query()->where('status', OrderStatus::Pending)->count(),
            'pendingChart' => $pendingChart,
            'thisWeekRevenue' => $this->sumRange($revenueByDate, $weekStart, $weekEnd),
            'lastWeekRevenue' => $this->sumRange($revenueByDate, $lastWeekStart, $lastWeekEnd),
            'revenueChart' => $revenueChart,
            'viewsToday' => $viewsChart[6] ?? 0,
            'viewsChart' => $viewsChart,
        ];
    }

    /** @return array<string, int> */
    private function ordersByDeliveryDate(Carbon $start, Carbon $end): array
    {
        return Order::query()
            ->whereBetween('delivery_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->toBase()
            ->selectRaw('DATE(delivery_date) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->pluck('aggregate', 'date')
            ->mapWithKeys(fn (mixed $count, mixed $date): array => [
                Arr::string(['date' => $date], 'date') => Arr::integer(['count' => $count], 'count', 0),
            ])
            ->all();
    }

    /** @return array<string, int> */
    private function pendingOrdersByCreatedDate(Carbon $start, Carbon $end): array
    {
        return Order::query()
            ->where('status', OrderStatus::Pending)
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->toBase()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->pluck('aggregate', 'date')
            ->mapWithKeys(fn (mixed $count, mixed $date): array => [
                Arr::string(['date' => $date], 'date') => Arr::integer(['count' => $count], 'count', 0),
            ])
            ->all();
    }

    /** @return array<string, int> */
    private function storefrontViewsByDate(Carbon $start, Carbon $end): array
    {
        return PageView::query()
            ->whereNull('product_id')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->toBase()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->pluck('aggregate', 'date')
            ->mapWithKeys(fn (mixed $count, mixed $date): array => [
                Arr::string(['date' => $date], 'date') => Arr::integer(['count' => $count], 'count', 0),
            ])
            ->all();
    }

    /** @return list<string> */
    private function dateKeys(Carbon $start, Carbon $end): array
    {
        $dates = [];
        $date = $start->copy()->startOfDay();

        while ($date->lte($end)) {
            $dates[] = $date->toDateString();
            $date->addDay();
        }

        return $dates;
    }

    /**
     * @param list<string> $dates
     * @param array<string, int> $values
     * @return list<int>
     */
    private function integerChart(array $dates, array $values): array
    {
        return array_map(fn (string $date): int => $values[$date] ?? 0, $dates);
    }

    /** @param array<string, float> $revenue */
    private function sumRange(array $revenue, Carbon $start, Carbon $end): float
    {
        return array_sum(array_map(
            fn (string $date): float => $revenue[$date] ?? 0.0,
            $this->dateKeys($start, $end),
        ));
    }
}
