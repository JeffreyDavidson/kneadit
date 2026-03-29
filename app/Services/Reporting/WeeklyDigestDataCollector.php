<?php

namespace App\Services\Reporting;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Queries\AtRiskCustomersQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

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
                'total_revenue' => Number::currency((float) $totalRevenue),
                'new_customers' => $newCustomers,
                'avg_order_value' => Number::currency($avgOrderValue),
            ],
            'topProducts' => OrderItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                ->whereHas('order', fn (Builder $q) => $q->whereBetween('created_at', [$weekStart, $weekEnd]))
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->with('product')
                ->get(),
            'atRiskCustomers' => AtRiskCustomersQuery::get(config('analytics.at_risk_threshold_days', 30), 5),
            'upcomingCount' => Order::query()
                ->whereBetween('delivery_date', [$nextWeekStart, $nextWeekEnd])
                ->active()
                ->count(),
            'storeName' => settings('store_name', 'KneadIt Bakery'),
            'adminUrl' => url('/admin'),
        ];
    }
}
