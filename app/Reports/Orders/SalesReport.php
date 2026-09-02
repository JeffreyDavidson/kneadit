<?php

namespace App\Reports\Orders;

use App\Models\Orders\Order;
use App\Queries\Financial\ProductSalesQuery;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SalesReport
{
    /** @return array<string, mixed> */
    public function generate(DateRange $range): array
    {
        $orders = Order::query()
            ->active()
            ->paid()
            ->whereBetween('delivery_date', $range->toArray());

        $totalOrders = $orders->count();
        $totalRevenue = RevenueQuery::total($range);
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $ordersByStatus = (clone $orders)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topProducts = ProductSalesQuery::topByRevenue($range)->all();

        // orders.total is bigint cents (migration 2026_04_22_201500); divide back
        // to dollars for the row payload.
        $revenueByDay = (clone $orders)
            ->select(DB::raw('DATE(delivery_date) as date'), DB::raw('SUM(total) as revenue_cents'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function (Order $row): array {
                $date = $row->getAttribute('date');

                return [
                    'date' => is_string($date) ? $date : '',
                    'revenue' => Arr::integer($row->getAttributes(), 'revenue_cents', 0) / 100,
                ];
            })
            ->all();

        return ['totalOrders' => $totalOrders, 'totalRevenue' => $totalRevenue, 'avgOrderValue' => $avgOrderValue, 'ordersByStatus' => $ordersByStatus, 'topProducts' => $topProducts, 'revenueByDay' => $revenueByDay];
    }
}
