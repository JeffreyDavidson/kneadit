<?php

namespace App\Queries\Customers;

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class CustomerDirectoryStatsQuery
{
    /** @return array{total_customers: int, avg_lifetime_value: string, at_risk_count: int, top_customer_name: string, top_customer_value: string} */
    public static function get(): array
    {
        $totalCustomers = Customer::query()->count();

        $avgLifetimeValue = (float) Order::query()->active()
            ->selectRaw('AVG(customer_total) as avg_ltv')
            ->fromSub(
                Order::query()->active()->selectRaw('customer_id, SUM(total) as customer_total')->groupBy('customer_id'),
                'customer_totals',
            )
            ->value('avg_ltv');

        $atRiskDays = (int) (string) config('analytics.at_risk_threshold_days', 30);
        $atRiskCount = AtRiskCustomersQuery::count($atRiskDays);

        $topCustomer = Customer::query()
            ->withSum(['orders' => fn (Builder $q) => $q->active()], 'total')
            ->orderByDesc('orders_sum_total')
            ->first();

        return [
            'total_customers' => $totalCustomers,
            'avg_lifetime_value' => (string) Number::currency($avgLifetimeValue),
            'at_risk_count' => $atRiskCount,
            'top_customer_name' => $topCustomer->name ?? 'N/A',
            'top_customer_value' => (string) Number::currency($topCustomer->orders_sum_total ?? 0),
        ];
    }
}
