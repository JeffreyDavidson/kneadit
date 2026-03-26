<?php

namespace App\Services\Reporting;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WeeklyDigestDataCollector
{
    /** @return array{stats: array<string, mixed>, topProducts: Collection<int, OrderItem>, atRiskCustomers: Collection<int, Customer>, upcomingCount: int, storeName: string, adminUrl: string} */
    public function collect(): array
    {
        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd = now()->subWeek()->endOfWeek();
        $nextWeekStart = now()->startOfWeek();
        $nextWeekEnd = now()->endOfWeek();

        $weekOrders = Order::query()->whereBetween('created_at', [$weekStart, $weekEnd]);

        $totalOrders = (clone $weekOrders)->count();
        $totalRevenue = (clone $weekOrders)->sum('total');
        $newCustomers = Customer::query()->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'stats' => [
                'total_orders' => $totalOrders,
                'total_revenue' => number_format((float) $totalRevenue, 2),
                'new_customers' => $newCustomers,
                'avg_order_value' => number_format($avgOrderValue, 2),
            ],
            'topProducts' => OrderItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                ->whereHas('order', fn (Builder $q) => $q->whereBetween('created_at', [$weekStart, $weekEnd]))
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->with('product')
                ->get(),
            'atRiskCustomers' => Customer::query()
                ->whereHas('orders')
                ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays(config('analytics.at_risk_threshold_days', 30))))
                ->limit(5)
                ->get(),
            'upcomingCount' => Order::query()
                ->whereBetween('delivery_date', [$nextWeekStart, $nextWeekEnd])
                ->active()
                ->count(),
            'storeName' => settings('store_name', 'KneadIt Bakery'),
            'adminUrl' => url('/admin'),
        ];
    }
}
