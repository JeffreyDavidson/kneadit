<?php

namespace App\Queries\Analytics;

use App\Models\Orders\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

final class CustomerInsightsQuery
{
    /**
     * @return array{total_with_orders: int, repeat_customers: int}
     */
    public function repeatCustomerCounts(): array
    {
        $customerOrderCounts = Order::query()
            ->active()
            ->select('customer_id')
            ->selectRaw('COUNT(*) as order_count')
            ->groupBy('customer_id');

        $counts = Order::query()
            ->fromSub($customerOrderCounts, 'customer_order_counts')
            ->toBase()
            ->selectRaw('COUNT(*) as total_with_orders')
            ->selectRaw('SUM(CASE WHEN order_count >= 2 THEN 1 ELSE 0 END) as repeat_customers')
            ->first();

        return [
            'total_with_orders' => Arr::integer(['value' => $counts->total_with_orders ?? 0], 'value', 0),
            'repeat_customers' => Arr::integer(['value' => $counts->repeat_customers ?? 0], 'value', 0),
        ];
    }

    /**
     * @return array{this_month: float, last_month: float}
     */
    public function averageOrderValues(?Carbon $now = null): array
    {
        $now ??= now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $nextMonthStart = $thisMonthStart->copy()->addMonth();
        $lastMonthStart = $thisMonthStart->copy()->subMonth();

        $averages = Order::query()
            ->active()
            ->where('created_at', '>=', $lastMonthStart)
            ->where('created_at', '<', $nextMonthStart)
            ->toBase()
            ->selectRaw(
                'AVG(CASE WHEN created_at >= ? AND created_at < ? THEN total END) as this_month',
                [$thisMonthStart, $nextMonthStart],
            )
            ->selectRaw(
                'AVG(CASE WHEN created_at >= ? AND created_at < ? THEN total END) as last_month',
                [$lastMonthStart, $thisMonthStart],
            )
            ->first();

        return [
            'this_month' => Arr::float(['value' => $averages->this_month ?? 0], 'value', 0.0),
            'last_month' => Arr::float(['value' => $averages->last_month ?? 0], 'value', 0.0),
        ];
    }
}
