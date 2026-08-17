<?php

namespace App\Queries\Financial;

use App\Models\Orders\Order;
use App\ValueObjects\DateRange;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class RevenueQuery
{
    /**
     * Get total revenue for a date range.
     *
     * @param DateRange|array<int, string> $range
     */
    public static function total(DateRange|array $range): float
    {
        $dates = self::bounds($range);

        // orders.total is now bigint cents (see migration 2026_04_22_201500);
        // convert the aggregate back to dollars for callers that still expect a float.
        return (int) Order::query()
            ->active()->paid()
            ->whereBetween('delivery_date', $dates)
            ->sum('total') / 100;
    }

    /**
     * Get daily revenue breakdown for a date range.
     *
     * @param DateRange|array<int, string> $range
     * @return array<string, float>
     */
    public static function dailyBreakdown(DateRange|array $range): array
    {
        $dates = self::bounds($range);

        return Order::query()
            ->active()->paid()
            ->whereBetween('delivery_date', $dates)
            ->toBase()
            ->selectRaw('DATE(delivery_date) as date, SUM(total) as revenue_cents')
            ->groupBy('date')
            ->pluck('revenue_cents', 'date')
            ->map(fn (mixed $v): float => Arr::integer(['value' => $v], 'value', 0) / 100)
            ->all();
    }

    /**
     * Get order count for a date range.
     *
     * @param DateRange|array<int, string> $range
     */
    public static function orderCount(DateRange|array $range): int
    {
        $dates = self::bounds($range);

        return Order::query()
            ->active()
            ->whereBetween('delivery_date', $dates)
            ->count();
    }

    /**
     * @param DateRange|array<int, string> $range
     * @return array{Carbon, Carbon}
     */
    private static function bounds(DateRange|array $range): array
    {
        $dates = $range instanceof DateRange ? $range->toArray() : $range;

        return [
            Date::parse($dates[0])->startOfDay(),
            Date::parse($dates[1])->endOfDay(),
        ];
    }
}
