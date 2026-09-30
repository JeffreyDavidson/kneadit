<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Models\Operations\BusinessSchedule;
use App\Services\Settings\TenantSettings;
use Carbon\CarbonImmutable;

/**
 * The first delivery date a customer can choose right now. Staff-created
 * orders don't use it.
 */
final readonly class EarliestDeliveryDate
{
    public function __construct(
        private TenantSettings $settings,
        private BakeryClock $clock,
    ) {}

    /**
     * The bakery's local today plus the lead-time days. Once today's Schedule
     * Manager order cutoff has passed, the order counts as placed tomorrow.
     */
    public function get(): CarbonImmutable
    {
        $now = $this->clock->now()->toImmutable();
        $orderingDay = $now->startOfDay();
        $cutoff = BusinessSchedule::query()->forDay($now->dayOfWeek)->first()?->order_cutoff_time;

        if ($cutoff !== null && $now->gte($orderingDay->setTimeFromTimeString($cutoff))) {
            $orderingDay = $orderingDay->addDay();
        }

        return $orderingDay->addDays($this->settings->leadTimeDays());
    }
}
