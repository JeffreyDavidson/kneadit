<?php

namespace App\Services\Inventory;

use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\Models\Operations\CapacityLimit;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class CapacityCalculator
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

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

        return $this->settings->orders->defaultDailyCapacity;
    }

    public function isAvailable(Carbon|string $date): bool
    {
        $date = Date::parse($date);

        if (BlockedDate::query()->where('date', $date->toDateString())->where('is_all_day', true)->exists()) {
            return false;
        }

        $schedule = BusinessSchedule::query()
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        if ($schedule && ! $schedule->is_open) {
            return false;
        }

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
