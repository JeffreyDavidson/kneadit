<?php

namespace App\Services\Inventory;

use App\Models\Orders\Order;
use App\Queries\Scheduling\DateCapacityRules;
use App\Services\Settings\TenantSettings;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

class CapacityCalculator
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    /**
     * The date's order limit per DateCapacityRules::maxOrders(), or the
     * tenant default when no rule sets one.
     */
    public function getMaxOrders(Carbon|string $date): int
    {
        $carbon = Date::parse($date);

        return $this->maxOrders(DateCapacityRules::between($carbon, $carbon), $carbon);
    }

    public function isAvailable(Carbon|string $date): bool
    {
        $carbon = Date::parse($date);
        $rules = DateCapacityRules::between($carbon, $carbon);

        if (! $rules->status($carbon)->open) {
            return false;
        }

        return $this->ordersOnDate($carbon) < $this->maxOrders($rules, $carbon);
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

    private function maxOrders(DateCapacityRules $rules, CarbonInterface $date): int
    {
        return $rules->maxOrders($date) ?? $this->settings->orders->defaultDailyCapacity;
    }
}
