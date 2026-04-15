<?php

namespace App\Services\Scheduling;

use App\Enums\Orders\OrderStatus;
use App\Models\Operations\BlockedDate;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Date;

class AvailabilityService
{
    public function __construct(
        protected ScheduleService $scheduleService,
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

        for ($i = 0; $i < $days; $i++) {
            $date = $today->copy()->addDays($i);
            $dateStr = $date->toDateString();

            $dates[] = $this->checkDate($dateStr, (int) $date->dayOfWeek);
        }

        return $dates;
    }

    /**
     * @return array{date: string, available: bool, reason: string, remaining_capacity: int}
     */
    private function checkDate(string $dateStr, int $dayOfWeek): array
    {
        $blocked = BlockedDate::query()->where('date', $dateStr)->where('is_all_day', true)->first();

        if ($blocked) {
            return ['date' => $dateStr, 'available' => false, 'reason' => $blocked->reason ?? 'Blocked', 'remaining_capacity' => 0];
        }

        $schedule = $this->scheduleService->forDay($dayOfWeek);

        if (! ($schedule->is_open ?? false)) {
            return ['date' => $dateStr, 'available' => false, 'reason' => 'Closed', 'remaining_capacity' => 0];
        }

        $maxOrders = $schedule->max_orders ?? (int) settings('default_daily_capacity', 20);
        $currentOrders = Order::query()->whereDate('delivery_date', $dateStr)
            ->whereNotIn('status', [OrderStatus::Cancelled])
            ->count();
        $remaining = max(0, $maxOrders - $currentOrders);

        return [
            'date' => $dateStr,
            'available' => $remaining > 0,
            'reason' => $remaining > 0 ? 'Open' : 'Fully booked',
            'remaining_capacity' => $remaining,
        ];
    }
}
