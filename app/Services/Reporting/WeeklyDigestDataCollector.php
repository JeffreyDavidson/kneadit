<?php

namespace App\Services\Reporting;

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Presenters\CustomerPresenter;
use App\Queries\Customers\AtRiskCustomersQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class WeeklyDigestDataCollector
{
    /** @return array{stats: array<string, mixed>, topProducts: Collection<int, OrderItem>, atRiskCustomers: SupportCollection<int, array{name: string, days_since_last_order: ?int}>, upcomingCount: int, storeName: string, adminUrl: string} */
    public function collect(): array
    {
        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd = now()->subWeek()->endOfWeek();
        $nextWeekStart = now()->startOfWeek();
        $nextWeekEnd = now()->endOfWeek();

        $weekOrders = Order::query()->whereBetween('created_at', [$weekStart, $weekEnd]);

        $totalOrders = (clone $weekOrders)->count();
        // orders.total is bigint cents (migration 2026_04_22_201500).
        $totalRevenue = (int) (clone $weekOrders)->sum('total') / 100;
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
            'atRiskCustomers' => AtRiskCustomersQuery::get(config('analytics.at_risk_threshold_days', 30), 5)
                ->map(fn (Customer $customer) => [
                    'name' => $customer->name,
                    'days_since_last_order' => CustomerPresenter::for($customer)->daysSinceLastOrder(),
                ]),
            'upcomingCount' => Order::query()
                ->whereBetween('delivery_date', [$nextWeekStart, $nextWeekEnd])
                ->active()
                ->count(),
            'storeName' => app(\App\Services\Settings\TenantSettings::class)->store->name,
            'adminUrl' => url('/admin'),
        ];
    }
}
