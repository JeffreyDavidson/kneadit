<?php

namespace App\Reports;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
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
        $totalRevenue = $paidOrders->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $ordersByStatus = Order::query()->whereBetween('delivery_date', $range->toArray())
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topProducts = OrderItem::query()->whereIn('order_id', Order::query()->whereBetween('delivery_date', $range->toArray())->where('payment_status', PaymentStatus::Paid)->select('id'))
            ->select('product_id', DB::raw('SUM(quantity) as units_sold'), DB::raw('SUM(quantity * unit_price) as revenue'))
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn (OrderItem $item) => [
                'name' => $item->product->name ?? 'Deleted Product',
                'units_sold' => (int) $item->units_sold,
                'revenue' => (float) $item->revenue,
            ])
            ->toArray();

        $revenueByDay = Order::query()->whereBetween('delivery_date', $range->toArray())
            ->where('payment_status', PaymentStatus::Paid)
            ->select(DB::raw('DATE(delivery_date) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn (Order $row) => ['date' => $row->date, 'revenue' => (float) $row->revenue])
            ->all();

        return compact('totalOrders', 'totalRevenue', 'avgOrderValue', 'ordersByStatus', 'topProducts', 'revenueByDay');
    }
}
