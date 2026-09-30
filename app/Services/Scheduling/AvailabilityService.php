<?php

namespace App\Services\Scheduling;

use App\Models\Orders\Order;
use App\Queries\Scheduling\DateCapacityRules;
use App\Services\Settings\TenantSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

class AvailabilityService
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    /**
     * Get availability for the next N days.
     *
     * @return array<int, array{date: string, available: bool, reason: string, remaining_capacity: int}>
     */
    public function getAvailability(int $days = 30): array
    {
        $dates = [];
        $today = Date::today();
        $orderCounts = collect();

        if ($days > 0) {
            $lastDate = $today->copy()->addDays($days - 1);
            $orderCounts = Order::query()
                ->active()
                ->whereBetween('delivery_date', [$today->toDateString(), $lastDate->toDateString()])
                ->selectRaw('delivery_date, COUNT(*) as active_orders')
                ->groupBy('delivery_date')
                ->toBase()
                ->get()
                ->mapWithKeys(function (object $row): array {
                    if (! is_string($row->delivery_date) || ! is_numeric($row->active_orders)) {
                        return [];
                    }

                    return [Date::parse($row->delivery_date)->toDateString() => (int) $row->active_orders];
                });
        }

        if ($days <= 0) {
            return $dates;
        }

        $rules = DateCapacityRules::between($today, $today->copy()->addDays($days - 1));

        for ($i = 0; $i < $days; $i++) {
            $date = $today->copy()->addDays($i);

            $dates[] = $this->checkDate(
                $date,
                $rules,
                (int) $orderCounts->get($date->toDateString(), 0),
            );
        }

        return $dates;
    }

    /**
     * @return array{date: string, available: bool, reason: string, remaining_capacity: int}
     */
    private function checkDate(CarbonInterface $date, DateCapacityRules $rules, int $currentOrders): array
    {
        $dateStr = $date->toDateString();
        $status = $rules->status($date);

        if (! $status->open) {
            return ['date' => $dateStr, 'available' => false, 'reason' => $status->reason ?? 'Closed', 'remaining_capacity' => 0];
        }

        $maxOrders = $rules->maxOrders($date) ?? $this->settings->orders->defaultDailyCapacity;

        if ($maxOrders === 0) {
            return ['date' => $dateStr, 'available' => false, 'reason' => 'Not accepting orders', 'remaining_capacity' => 0];
        }

        $remaining = max(0, $maxOrders - $currentOrders);

        return [
            'date' => $dateStr,
            'available' => $remaining > 0,
            'reason' => $remaining > 0 ? 'Open' : 'Fully booked',
            'remaining_capacity' => $remaining,
        ];
    }
}
