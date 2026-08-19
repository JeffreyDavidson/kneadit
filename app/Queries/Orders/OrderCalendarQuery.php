<?php

namespace App\Queries\Orders;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class OrderCalendarQuery
{
    /** @return Collection<string, int> */
    public static function countsForMonth(Carbon $month): Collection
    {
        return Order::query()
            ->whereBetween('delivery_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->selectRaw('DATE(delivery_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->map(fn (mixed $count): int => is_numeric($count) ? (int) $count : 0);
    }

    /** @return EloquentCollection<int, Order> */
    public static function ordersForDate(string $date): EloquentCollection
    {
        return Order::query()
            ->with(['customer', 'orderItems.product'])
            ->whereDate('delivery_date', $date)
            ->orderBy('delivery_time')
            ->get();
    }
}
