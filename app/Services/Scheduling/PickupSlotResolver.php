<?php

namespace App\Services\Scheduling;

use App\Enums\Orders\DeliveryType;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

/**
 * Generates the list of pickup time slots available for a given date.
 *
 * Slots are derived from the BusinessSchedule's open/close times for the
 * day-of-week, stepped by the configured interval, and filtered down to
 * those still under the per-slot booking cap.
 */
class PickupSlotResolver
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    /**
     * @return array<int, string> e.g. ['07:00', '07:30', '08:00']
     */
    public function availableSlots(string $dateString): array
    {
        if (! $this->settings->orders->pickupSlotsEnabled) {
            return [];
        }

        $date = Date::parse($dateString);
        $schedule = BusinessSchedule::query()->forDay((int) $date->dayOfWeek)->first();

        if ($schedule === null || ! $schedule->is_open || ! $schedule->open_time || ! $schedule->close_time) {
            return [];
        }

        $interval = max(5, $this->settings->orders->pickupSlotIntervalMinutes);
        $maxPerSlot = max(1, $this->settings->orders->pickupSlotMaxPerWindow);

        $open = Date::parse($schedule->open_time);
        $close = Date::parse($schedule->close_time);

        $slots = [];
        $cursor = $open->copy();
        while ($cursor->lessThan($close)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($interval);
        }

        if ($slots === []) {
            return [];
        }

        $bookedCounts = Order::query()
            ->whereDate('delivery_date', $date->toDateString())
            ->where('delivery_type', DeliveryType::Pickup->value)
            ->active()
            ->get(['delivery_time'])
            ->groupBy(fn (Order $order): string => $order->delivery_time?->format('H:i') ?? '')
            ->map->count();

        return array_values(array_filter(
            $slots,
            fn (string $slot): bool => ($bookedCounts[$slot] ?? 0) < $maxPerSlot,
        ));
    }
}
