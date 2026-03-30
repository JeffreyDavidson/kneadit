<?php

namespace App\Queries;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DriverDeliveryQuery
{
    /**
     * Get orders ready for delivery on a specific date.
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
