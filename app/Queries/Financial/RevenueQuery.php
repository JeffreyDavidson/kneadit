<?php

namespace App\Queries\Financial;

use App\Models\Orders\Order;
use App\Support\DatabaseValue;
use App\ValueObjects\DateRange;

class RevenueQuery
{
    /**
     * Get total revenue for a date range.
     *
     * @param DateRange|array<int, string> $range
     */
    public static function total(DateRange|array $range): float
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        // orders.total is now bigint cents (see migration 2026_04_22_201500);
        // convert the aggregate back to dollars for callers that still expect a float.
        return DatabaseValue::int(Order::query()
            ->active()->paid()
            ->whereBetween('delivery_date', $dates)
            ->sum('total')) / 100;
    }

    /**
     * Get daily revenue breakdown for a date range.
     *
     * @param DateRange|array<int, string> $range
     * @return array<string, float>
     */
    public static function dailyBreakdown(DateRange|array $range): array
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        return Order::query()
            ->active()->paid()
            ->whereBetween('delivery_date', $dates)
            ->toBase()
            ->selectRaw('DATE(delivery_date) as date, SUM(total) as revenue_cents')
            ->groupBy('date')
            ->pluck('revenue_cents', 'date')
            ->map(fn (mixed $v): float => DatabaseValue::int($v) / 100)
            ->all();
    }

    /**
     * Get order count for a date range.
     *
     * @param DateRange|array<int, string> $range
     */
    public static function orderCount(DateRange|array $range): int
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        return Order::query()
            ->active()
            ->whereBetween('delivery_date', $dates)
            ->count();
    }
}
