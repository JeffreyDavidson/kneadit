<?php

namespace App\Queries\Production;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class WeeklyProductionOrdersQuery
{
    /** @return Collection<int, Order> */
    public static function between(Carbon $startDate, Carbon $endDate): Collection
    {
        return Order::query()
            ->with(['customer', 'orderItems.product.recipes'])
            ->whereBetween('delivery_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->oldest('delivery_date')
            ->orderBy('delivery_time')
            ->get();
    }
}
