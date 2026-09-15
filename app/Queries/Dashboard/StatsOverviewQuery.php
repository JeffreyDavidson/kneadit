<?php

namespace App\Queries\Dashboard;

use App\DataTransferObjects\Analytics\DateSeries;
use App\Enums\Orders\OrderStatus;
use App\Models\Engagement\PageView;
use App\Models\Orders\Order;
use App\Queries\Financial\RevenueQuery;
use Illuminate\Database\Eloquent\Builder;
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

        $dateSeries = DateSeries::between($chartStart, $today);
        $dates = $dateSeries->dates();
        $ordersByDate = $this->ordersByDeliveryDate($chartStart, $today);
        $pendingByDate = $this->pendingOrdersByCreatedDate($chartStart, $today);
        $viewsByDate = $this->storefrontViewsByDate($chartStart, $today);
        $revenueByDate = RevenueQuery::dailyBreakdown([
            $lastWeekStart->toDateString(),
            $weekEnd->toDateString(),
        ]);

        $ordersChart = $dateSeries->fillIntegers($ordersByDate);
        $pendingChart = $dateSeries->fillIntegers($pendingByDate);
        $viewsChart = $dateSeries->fillIntegers($viewsByDate);
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
        return $this->countByDate(Order::query(), 'delivery_date', $start, $end);
    }

    /** @return array<string, int> */
    private function pendingOrdersByCreatedDate(Carbon $start, Carbon $end): array
    {
        return $this->countByDate(
            Order::query()->where('status', OrderStatus::Pending),
            'created_at',
            $start,
            $end,
        );
    }

    /** @return array<string, int> */
    private function storefrontViewsByDate(Carbon $start, Carbon $end): array
    {
        return $this->countByDate(
            PageView::query()->whereNull('product_id'),
            'created_at',
            $start,
            $end,
        );
    }

    /**
     * @return array<string, int>
     */
    private function countByDate(Builder $query, string $dateColumn, Carbon $start, Carbon $end): array
    {
        return $query
            ->whereBetween($dateColumn, [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->toBase()
            ->selectRaw("DATE({$dateColumn}) as date, COUNT(*) as aggregate")
            ->groupBy('date')
            ->pluck('aggregate', 'date')
            ->mapWithKeys(fn (mixed $count, mixed $date): array => [
                Arr::string(['date' => $date], 'date') => Arr::integer(['count' => $count], 'count', 0),
            ])
            ->all();
    }

    /** @param array<string, float> $revenue */
    private function sumRange(array $revenue, Carbon $start, Carbon $end): float
    {
        return array_sum(array_map(
            fn (string $date): float => $revenue[$date] ?? 0.0,
            DateSeries::between($start, $end)->dates(),
        ));
    }
}
