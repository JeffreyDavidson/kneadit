<?php

namespace App\Services\Scheduling;

use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use App\Queries\Scheduling\DateOpenStatusQuery;
use App\Services\Settings\TenantSettings;
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

        for ($i = 0; $i < $days; $i++) {
            $date = $today->copy()->addDays($i);
            $dateStr = $date->toDateString();

            $dates[] = $this->checkDate(
                $dateStr,
                (int) $date->dayOfWeek,
                (int) $orderCounts->get($dateStr, 0),
            );
        }

        return $dates;
    }

    /**
     * @return array{date: string, available: bool, reason: string, remaining_capacity: int}
     */
    private function checkDate(string $dateStr, int $dayOfWeek, int $currentOrders): array
    {
        $status = DateOpenStatusQuery::forDate($dateStr);

        if (! $status->open) {
            return ['date' => $dateStr, 'available' => false, 'reason' => $status->reason ?? 'Closed', 'remaining_capacity' => 0];
        }

        $schedule = BusinessSchedule::query()->forDay($dayOfWeek)->first();
        $maxOrders = $schedule->max_orders ?? $this->settings->orders->defaultDailyCapacity;
        $remaining = max(0, $maxOrders - $currentOrders);

        return [
            'date' => $dateStr,
            'available' => $remaining > 0,
            'reason' => $remaining > 0 ? 'Open' : 'Fully booked',
            'remaining_capacity' => $remaining,
        ];
    }
}
