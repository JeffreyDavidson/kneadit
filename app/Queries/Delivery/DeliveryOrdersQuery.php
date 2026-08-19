<?php

namespace App\Queries\Delivery;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Collection;

final class DeliveryOrdersQuery
{
    /** @return Collection<int, Order> */
    public static function forDate(string $date): Collection
    {
        return Order::query()
            ->with(['customer', 'orderItems'])
            ->whereDate('delivery_date', $date)
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->orderBy('delivery_time')
            ->get();
    }
}
