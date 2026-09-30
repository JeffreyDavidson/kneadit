<?php

namespace App\Services\Inventory;

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\CapacityLimit;
use App\Models\Orders\Order;
use App\Queries\Scheduling\DateOpenStatusQuery;
use App\Services\Settings\TenantSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class CapacityCalculator
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    /**
     * A limit for the exact date wins over the recurring weekday limit.
     */
    public function forDate(Carbon|string $date): ?CapacityLimit
    {
        $carbon = Date::parse($date);

        return CapacityLimit::query()->onSpecificDate($carbon)->first()
            ?? CapacityLimit::query()->onWeekday(DayOfWeek::phpWeekOrder()[$carbon->dayOfWeek])->first();
    }

    /**
     * A blocked limit allows no orders; a max of 0 means the tenant default.
     */
    public function getMaxOrders(Carbon|string $date): int
    {
        $limit = $this->forDate($date);

        if ($limit?->is_blocked) {
            return 0;
        }

        if ($limit instanceof CapacityLimit && $limit->max_orders > 0) {
            return $limit->max_orders;
        }

        return $this->settings->orders->defaultDailyCapacity;
    }

    public function isAvailable(Carbon|string $date): bool
    {
        if (! DateOpenStatusQuery::forDate($date)->open) {
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
