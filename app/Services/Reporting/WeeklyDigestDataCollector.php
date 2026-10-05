<?php

namespace App\Services\Reporting;

use App\DataTransferObjects\Platform\WeeklyDigestData;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Presenters\CustomerPresenter;
use App\Queries\Customers\AtRiskCustomersQuery;
use App\Queries\Reporting\WeeklyDigestQuery;
use App\Services\Scheduling\BakeryClock;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Config;

class WeeklyDigestDataCollector
{
    public function __construct(
        private readonly TenantSettings $settings,
        private readonly BakeryClock $clock,
    ) {}

    public function collect(): WeeklyDigestData
    {
        // The weeks are bakery-local Monday to Sunday. created_at holds UTC instants, so those bounds are converted; delivery_date is a plain date and uses the local dates.
        $now = $this->clock->now();
        $weekStart = $now->copy()->subWeek()->startOfWeek();
        $weekEnd = $now->copy()->subWeek()->endOfWeek();
        $thisWeekStart = $now->copy()->startOfWeek();
        $thisWeekEnd = $now->copy()->endOfWeek();
        $weekStartUtc = $weekStart->copy()->utc();
        $weekEndUtc = $weekEnd->copy()->utc();

        $weekOrders = Order::query()->whereBetween('created_at', [$weekStartUtc, $weekEndUtc]);
        $weekOrderStats = $weekOrders
            ->toBase()
            ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue')
            ->first();
        $totalOrdersValue = $weekOrderStats?->total_orders;
        $totalOrders = is_numeric($totalOrdersValue) ? (int) $totalOrdersValue : 0;
        // orders.total is bigint cents (migration 2026_04_22_201500).
        $totalRevenueValue = $weekOrderStats?->total_revenue;
        $totalRevenue = Money::fromCents(is_numeric($totalRevenueValue) ? (int) $totalRevenueValue : 0);
        $newCustomers = Customer::query()->whereBetween('created_at', [$weekStartUtc, $weekEndUtc])->count();
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
            topProducts: WeeklyDigestQuery::topProducts($weekStartUtc, $weekEndUtc),
            atRiskCustomers: AtRiskCustomersQuery::get(Config::integer('analytics.at_risk_threshold_days', 30), 5)
                ->map(fn (Customer $customer): array => [
                    'name' => $customer->name,
                    'days_since_last_order' => CustomerPresenter::for($customer)->daysSinceLastOrder(),
                ]),
            upcomingCount: Order::query()
                ->whereDate('delivery_date', '>=', $thisWeekStart)
                ->whereDate('delivery_date', '<=', $thisWeekEnd)
                ->active()
                ->count(),
            storeName: $this->settings->store->name,
            adminUrl: url('/admin'),
        );
    }
}
