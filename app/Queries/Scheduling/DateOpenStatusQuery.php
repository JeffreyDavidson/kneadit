<?php

namespace App\Queries\Scheduling;

use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\ValueObjects\DateOpenStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class DateOpenStatusQuery
{
    /**
     * Resolve whether a date is open for business.
     *
     * Checks BlockedDate (all-day blocks) first, then the day-of-week
     * BusinessSchedule. Returns a status VO with a display-ready reason
     * when the date is not open.
     */
    public static function forDate(Carbon|string $date): DateOpenStatus
    {
        $carbon = Date::parse($date);

        $blocked = BlockedDate::query()
            ->whereDate('date', $carbon)
            ->where('is_all_day', true)
            ->first();

        if ($blocked) {
            return DateOpenStatus::blocked($blocked->reason);
        }

        $schedule = BusinessSchedule::query()->forDay($carbon->dayOfWeek)->first();

        if ($schedule && ! $schedule->is_open) {
            return DateOpenStatus::closed();
        }

        return DateOpenStatus::open();
    }
}
