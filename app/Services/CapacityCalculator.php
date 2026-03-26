<?php

namespace App\Services;

use App\Models\CapacityLimit;
use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class CapacityCalculator
{
    public function forDate(Carbon|string $date): ?CapacityLimit
    {
        return CapacityLimit::query()->whereDate('date', Date::parse($date))->first();
    }

    public function getMaxOrders(Carbon|string $date): int
    {
        $limit = $this->forDate($date);

        if ($limit && $limit->max_orders > 0) {
            return $limit->max_orders;
        }

        return (int) Setting::get('default_daily_capacity', 20);
    }

    public function isAvailable(Carbon|string $date): bool
    {
        return $this->ordersOnDate($date) < $this->getMaxOrders($date);
    }

    public function remainingSlots(Carbon|string $date): int
    {
        return max(0, $this->getMaxOrders($date) - $this->ordersOnDate($date));
    }

    public function ordersOnDate(Carbon|string $date): int
    {
        return Order::query()->whereDate('delivery_date', Date::parse($date))
            ->active()
            ->count();
    }

    public function usagePercent(Carbon|string $date): float
    {
        $maxOrders = $this->getMaxOrders($date);
        if ($maxOrders <= 0) {
            return 0.0;
        }

        return min(100, ($this->ordersOnDate($date) / $maxOrders) * 100);
    }
}
