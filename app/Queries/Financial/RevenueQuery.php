<?php

namespace App\Queries\Financial;

use App\Models\Orders\Order;
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

        return (float) Order::query()
            ->active()
            ->whereBetween('delivery_date', $dates)
            ->sum('total');
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
            ->active()
            ->whereBetween('delivery_date', $dates)
            ->toBase()
            ->selectRaw('DATE(delivery_date) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date')
            ->map(fn (mixed $v): float => (float) $v)
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
