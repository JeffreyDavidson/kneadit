<?php

namespace App\Services\Reporting;

use App\DataTransferObjects\Reporting\WeeklyDigestData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Presenters\CustomerPresenter;
use App\Queries\Customers\AtRiskCustomersQuery;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class WeeklyDigestDataCollector
{
    public function collect(): WeeklyDigestData
    {
        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd = now()->subWeek()->endOfWeek();
        $nextWeekStart = now()->startOfWeek();
        $nextWeekEnd = now()->endOfWeek();

        $weekOrders = Order::query()->whereBetween('created_at', [$weekStart, $weekEnd]);

        $totalOrders = (clone $weekOrders)->count();
        // orders.total is bigint cents (migration 2026_04_22_201500).
        $totalRevenue = Money::fromCents((int) (clone $weekOrders)->sum('total'));
        $newCustomers = Customer::query()->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $avgOrderValue = $totalOrders > 0
            ? Money::fromCents((int) round($totalRevenue->cents() / $totalOrders))
            : Money::zero();

        return new WeeklyDigestData(
            stats: [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'new_customers' => $newCustomers,
                'avg_order_value' => $avgOrderValue,
            ],
            topProducts: OrderItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                ->whereHas('order', fn (Builder $q) => $q->whereBetween('created_at', [$weekStart, $weekEnd]))
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->with('product')
                ->get(),
            atRiskCustomers: AtRiskCustomersQuery::get(Config::integer('analytics.at_risk_threshold_days', 30), 5)
                ->map(fn (Customer $customer) => [
                    'name' => $customer->name,
                    'days_since_last_order' => CustomerPresenter::for($customer)->daysSinceLastOrder(),
                ]),
            upcomingCount: Order::query()
                ->whereBetween('delivery_date', [$nextWeekStart, $nextWeekEnd])
                ->active()
                ->count(),
            storeName: resolve(\App\Services\Settings\TenantSettings::class)->store->name,
            adminUrl: url('/admin'),
        );
    }
}
