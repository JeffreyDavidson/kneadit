<?php

namespace App\Queries\Analytics;

use App\Models\Orders\Order;
use App\Services\Scheduling\BakeryClock;
use App\ValueObjects\Money;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

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
     * @return array{this_month: Money, last_month: Money}
     */
    public function averageOrderValues(?Carbon $now = null): array
    {
        $now ??= resolve(BakeryClock::class)->now();
        $appTimezone = Config::string('app.timezone');
        $localMonthStart = $now->copy()->startOfMonth();
        $thisMonthStart = $localMonthStart->copy()->setTimezone($appTimezone);
        $nextMonthStart = $localMonthStart->copy()->addMonth()->setTimezone($appTimezone);
        $lastMonthStart = $localMonthStart->copy()->subMonth()->setTimezone($appTimezone);

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

        // orders.total is bigint cents, so AVG(total) is an average in cents.
        return [
            'this_month' => $this->averageInCents($averages->this_month ?? 0),
            'last_month' => $this->averageInCents($averages->last_month ?? 0),
        ];
    }

    private function averageInCents(mixed $average): Money
    {
        return Money::fromCents(is_numeric($average) ? (int) round((float) $average) : 0);
    }
}
