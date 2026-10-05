<?php

namespace App\Queries\Orders;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DriverDeliveryQuery
{
    /**
     * Get ready orders to deliver on a specific date.
     *
     * @return Collection<int, Order>
     */
    public static function forDate(Carbon $date): Collection
    {
        return Order::query()
            ->forDeliveryOnDate($date)
            ->get();
    }
}
