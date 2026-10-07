<?php

namespace App\Services\Scheduling;

use App\Builders\Orders\OrderQueryBuilder;
use App\Enums\Orders\DeliveryType;
use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

/**
 * Generates the list of pickup time slots available for a given date.
 *
 * Slots are derived from the BusinessSchedule's open/close times for the
 * day-of-week (or, on a date with a partial-day BlockedDate, that date's
 * special open/close times), stepped by the configured interval, and
 * filtered down to those still ahead of bakery-local now (on today's date)
 * and under the per-slot booking cap.
 */
class PickupSlotResolver
{
    public function __construct(
        private readonly TenantSettings $settings,
        private readonly BakeryClock $clock,
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

        $specialHours = $this->specialHours($date->toDateString());
        $open = Date::parse($specialHours->open_time ?? $schedule->open_time);
        $close = Date::parse($specialHours->close_time ?? $schedule->close_time);

        $slots = [];
        $cursor = $open->copy();
        while ($cursor->lessThan($close)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($interval);
        }

        $slots = $this->withoutPassedSlots($slots, $date->toDateString());

        if ($slots === []) {
            return [];
        }

        $bookedCounts = $this->bookedCounts($date->toDateString());
        $maxPerSlot = $this->maxPerSlot();

        return array_values(array_filter(
            $slots,
            fn (string $slot): bool => ($bookedCounts[$slot] ?? 0) < $maxPerSlot,
        ));
    }

    /**
     * On the bakery's current day, slots that start before bakery-local now can no longer be picked up.
     *
     * @param  array<int, string>  $slots
     * @return array<int, string>
     */
    private function withoutPassedSlots(array $slots, string $date): array
    {
        $now = $this->clock->now();

        if ($date !== $now->toDateString()) {
            return $slots;
        }

        return array_values(array_filter(
            $slots,
            fn (string $slot): bool => $slot >= $now->format('H:i'),
        ));
    }

    /**
     * Whether a slot has reached the per-slot booking cap. Pass
     * `$lockForUpdate` inside a transaction to hold the counted rows until it
     * commits.
     */
    public function isFull(string $dateString, string $slot, bool $lockForUpdate = false): bool
    {
        return ($this->bookedCounts(Date::parse($dateString)->toDateString(), $lockForUpdate)[$slot] ?? 0) >= $this->maxPerSlot();
    }

    private function maxPerSlot(): int
    {
        return max(1, $this->settings->orders->pickupSlotMaxPerWindow);
    }

    /**
     * The open/close times of a partial-day BlockedDate on the date, if any.
     */
    private function specialHours(string $date): ?BlockedDate
    {
        return BlockedDate::query()
            ->whereDate('date', $date)
            ->where('is_all_day', false)
            ->whereNotNull('open_time')
            ->whereNotNull('close_time')
            ->orderBy('id')
            ->first();
    }

    /**
     * Active pickup orders on the date, counted per 'H:i' slot.
     *
     * @return array<array-key, int>
     */
    private function bookedCounts(string $date, bool $lockForUpdate = false): array
    {
        return Order::query()
            ->whereDate('delivery_date', $date)
            ->where('delivery_type', DeliveryType::Pickup->value)
            ->active()
            ->when($lockForUpdate, fn (OrderQueryBuilder $query): OrderQueryBuilder => $query->lockForUpdate())
            ->get(['delivery_time'])
            ->countBy(fn (Order $order): string => $order->delivery_time?->format('H:i') ?? '')
            ->all();
    }
}
