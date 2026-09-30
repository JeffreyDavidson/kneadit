<?php

namespace App\Services\Inventory;

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\BusinessSchedule;
use App\Models\Operations\CapacityLimit;
use App\Models\Operations\Holiday;
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
     * The first level with a limit wins, most specific first: capacity limit
     * for the date, active holiday, capacity limit for the weekday, Schedule
     * Manager, then the tenant default. A blocked capacity limit means 0; a
     * blank or 0 max means "no limit here" and falls through.
     */
    public function getMaxOrders(Carbon|string $date): int
    {
        $carbon = Date::parse($date);

        return $this->capacityLimitMax(CapacityLimit::query()->onSpecificDate($carbon)->first())
            ?? Holiday::query()->active()->onDate($carbon)->where('max_orders', '>', 0)->orderBy('max_orders')->first()->max_orders
            ?? $this->capacityLimitMax(CapacityLimit::query()->onWeekday(DayOfWeek::phpWeekOrder()[$carbon->dayOfWeek])->first())
            ?? BusinessSchedule::query()->forDay($carbon->dayOfWeek)->where('max_orders', '>', 0)->first()->max_orders
            ?? $this->settings->orders->defaultDailyCapacity;
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

    private function capacityLimitMax(?CapacityLimit $limit): ?int
    {
        if (! $limit instanceof CapacityLimit) {
            return null;
        }

        if ($limit->is_blocked) {
            return 0;
        }

        return $limit->max_orders > 0
            ? $limit->max_orders
            : null;
    }
}
