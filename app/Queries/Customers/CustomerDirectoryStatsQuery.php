<?php

namespace App\Queries\Customers;

use App\Builders\Orders\OrderQueryBuilder;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Number;

class CustomerDirectoryStatsQuery
{
    /** @return array{total_customers: int, avg_lifetime_value: string, at_risk_count: int, top_customer_name: string, top_customer_value: string} */
    public static function get(): array
    {
        $totalCustomers = Customer::query()->count();

        // orders.total is bigint cents (migration 2026_04_22_201500); divide back
        // to dollars at the boundary.
        $avgLifetimeValue = (int) Order::query()->active()
            ->selectRaw('AVG(customer_total) as avg_ltv')
            ->fromSub(
                Order::query()->active()->selectRaw('customer_id, SUM(total) as customer_total')->groupBy('customer_id'),
                'customer_totals',
            )
            ->value('avg_ltv') / 100;

        $atRiskDays = Config::integer('analytics.at_risk_threshold_days', 30);
        $atRiskCount = AtRiskCustomersQuery::count($atRiskDays);

        $topCustomer = Customer::query()
            ->withSum(['orders' => fn (OrderQueryBuilder $q) => $q->active()], 'total')
            ->orderByDesc('orders_sum_total')
            ->first();

        return [
            'total_customers' => $totalCustomers,
            'avg_lifetime_value' => (string) Number::currency($avgLifetimeValue),
            'at_risk_count' => $atRiskCount,
            'top_customer_name' => $topCustomer->name ?? 'N/A',
            'top_customer_value' => (string) Number::currency(((int) ($topCustomer->orders_sum_total ?? 0)) / 100),
        ];
    }
}
