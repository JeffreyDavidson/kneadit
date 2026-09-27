<?php

namespace App\Services\Reporting;

use App\DataTransferObjects\Platform\WeeklyDigestData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Presenters\CustomerPresenter;
use App\Queries\Customers\AtRiskCustomersQuery;
use App\Queries\Reporting\WeeklyDigestQuery;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Config;

class WeeklyDigestDataCollector
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    public function collect(): WeeklyDigestData
    {
        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd = now()->subWeek()->endOfWeek();
        $nextWeekStart = now()->startOfWeek();
        $nextWeekEnd = now()->endOfWeek();

        $weekOrders = Order::query()->whereBetween('created_at', [$weekStart, $weekEnd]);
        $weekOrderStats = $weekOrders
            ->toBase()
            ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue')
            ->first();
        $totalOrdersValue = $weekOrderStats?->total_orders;
        $totalOrders = is_numeric($totalOrdersValue) ? (int) $totalOrdersValue : 0;
        // orders.total is bigint cents (migration 2026_04_22_201500).
        $totalRevenueValue = $weekOrderStats?->total_revenue;
        $totalRevenue = Money::fromCents(is_numeric($totalRevenueValue) ? (int) $totalRevenueValue : 0);
        $newCustomers = Customer::query()->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $averageOrderValue = $totalOrders > 0
            ? $totalRevenue->multiply(1 / $totalOrders)
            : Money::zero();

        return new WeeklyDigestData(
            stats: [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'new_customers' => $newCustomers,
                'avg_order_value' => $averageOrderValue,
            ],
            topProducts: WeeklyDigestQuery::topProducts($weekStart, $weekEnd),
            atRiskCustomers: AtRiskCustomersQuery::get(Config::integer('analytics.at_risk_threshold_days', 30), 5)
                ->map(fn (Customer $customer): array => [
                    'name' => $customer->name,
                    'days_since_last_order' => CustomerPresenter::for($customer)->daysSinceLastOrder(),
                ]),
            upcomingCount: Order::query()
                ->whereBetween('delivery_date', [$nextWeekStart, $nextWeekEnd])
                ->active()
                ->count(),
            storeName: $this->settings->store->name,
            adminUrl: url('/admin'),
        );
    }
}
