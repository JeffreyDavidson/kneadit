<?php

namespace App\Reports\Orders;

use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Queries\Financial\ProductSalesQuery;
use App\Queries\Financial\RevenueQuery;
use App\ValueObjects\DateRange;
use Illuminate\Support\Facades\DB;

class SalesReport
{
    /** @return array<string, mixed> */
    public function generate(DateRange $range): array
    {
        $orders = Order::query()->whereBetween('delivery_date', $range->toArray());
        $paidOrders = (clone $orders)->where('payment_status', PaymentStatus::Paid);

        $totalOrders = $orders->count();
        $totalRevenue = RevenueQuery::total($range);
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $ordersByStatus = Order::query()->whereBetween('delivery_date', $range->toArray())
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topProducts = ProductSalesQuery::topByRevenue($range)->all();

        // orders.total is bigint cents (migration 2026_04_22_201500); divide back
        // to dollars for the row payload.
        $revenueByDay = Order::query()->whereBetween('delivery_date', $range->toArray())
            ->where('payment_status', PaymentStatus::Paid)
            ->select(DB::raw('DATE(delivery_date) as date'), DB::raw('SUM(total) as revenue_cents'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn (Order $row) => ['date' => $row->getAttribute('date'), 'revenue' => (int) $row->getAttribute('revenue_cents') / 100])
            ->all();

        return ['totalOrders' => $totalOrders, 'totalRevenue' => $totalRevenue, 'avgOrderValue' => $avgOrderValue, 'ordersByStatus' => $ordersByStatus, 'topProducts' => $topProducts, 'revenueByDay' => $revenueByDay];
    }
}
