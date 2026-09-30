<?php

declare(strict_types=1);

namespace App\Queries\Scheduling;

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\BlockedDate;
use App\Models\Operations\BusinessSchedule;
use App\Models\Operations\CapacityLimit;
use App\Models\Operations\Holiday;
use App\ValueObjects\DateOpenStatus;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

/**
 * Every rule that decides whether a delivery date is open and how many
 * orders it can take, loaded once for a date range (four queries however
 * long the range). The single source of the precedence used by checkout,
 * the order API and the storefront availability calendar.
 */
final readonly class DateCapacityRules
{
    /**
     * @param  Collection<string, BlockedDate>  $blockedDates  all-day blocks, keyed by Y-m-d
     * @param  Collection<int, BusinessSchedule>  $schedules  keyed by PHP day index (0 = Sunday)
     * @param  Collection<array-key, Collection<int, Holiday>>  $holidays  active holidays, grouped by Y-m-d
     * @param  Collection<string, CapacityLimit>  $dateLimits  keyed by Y-m-d
     * @param  Collection<string, CapacityLimit>  $weekdayLimits  keyed by DayOfWeek value
     */
    private function __construct(
        private Collection $blockedDates,
        private Collection $schedules,
        private Collection $holidays,
        private Collection $dateLimits,
        private Collection $weekdayLimits,
    ) {}

    public static function between(CarbonInterface $start, CarbonInterface $end): self
    {
        $limits = CapacityLimit::query()->applyingBetween($start, $end)->orderBy('id')->get();

        return new self(
            blockedDates: BlockedDate::query()
                ->where('is_all_day', true)
                ->whereDate('date', '>=', $start)
                ->whereDate('date', '<=', $end)
                ->orderBy('id')
                ->get()
                ->unique(fn (BlockedDate $blocked): string => $blocked->date->toDateString())
                ->keyBy(fn (BlockedDate $blocked): string => $blocked->date->toDateString()),
            schedules: BusinessSchedule::query()
                ->orderBy('id')
                ->get()
                ->unique('day_of_week')
                ->keyBy('day_of_week'),
            holidays: Holiday::query()
                ->active()
                ->betweenDates($start, $end)
                ->orderBy('id')
                ->get()
                ->toBase()
                ->groupBy(fn (Holiday $holiday): string => $holiday->date->toDateString()),
            dateLimits: self::limitsByDate($limits),
            weekdayLimits: $limits
                ->filter(fn (CapacityLimit $limit): bool => $limit->specific_date === null)
                ->unique('day_of_week')
                ->keyBy('day_of_week'),
        );
    }

    /**
     * Closed by an all-day blocked date, then a closed Schedule Manager day,
     * then an active holiday whose order deadline has passed (the deadline
     * day itself stays open).
     */
    public function status(CarbonInterface $date): DateOpenStatus
    {
        $blocked = $this->blockedDates->get($date->toDateString());

        if ($blocked instanceof BlockedDate) {
            return DateOpenStatus::blocked($blocked->reason);
        }

        $schedule = $this->schedules->get($date->dayOfWeek);

        if ($schedule instanceof BusinessSchedule && ! $schedule->is_open) {
            return DateOpenStatus::closed();
        }

        $pastDeadline = $this->holidaysOn($date)->first(
            fn (Holiday $holiday): bool => $holiday->order_deadline?->lt(Date::today()) ?? false,
        );

        if ($pastDeadline instanceof Holiday) {
            return DateOpenStatus::orderingClosed($pastDeadline->name);
        }

        return DateOpenStatus::open();
    }

    /**
     * The first level with a limit wins, most specific first: capacity limit
     * for the date, active holiday (lowest max if several), capacity limit
     * for the weekday, then Schedule Manager. A blocked capacity limit means
     * 0; a blank or 0 max falls through. Null means no rule sets a limit, so
     * the caller applies the tenant default.
     */
    public function maxOrders(CarbonInterface $date): ?int
    {
        return $this->capacityLimitMax($this->dateLimits->get($date->toDateString()))
            ?? $this->holidaysOn($date)
                ->filter(fn (Holiday $holiday): bool => $holiday->max_orders > 0)
                ->sortBy('max_orders')
                ->first()
                ->max_orders
            ?? $this->capacityLimitMax($this->weekdayLimits->get(DayOfWeek::phpWeekOrder()[$date->dayOfWeek]->value))
            ?? $this->scheduleMax($date);
    }

    /**
     * The first specific-date limit for each date.
     *
     * @param  Collection<int, CapacityLimit>  $limits
     * @return Collection<string, CapacityLimit>
     */
    private static function limitsByDate(Collection $limits): Collection
    {
        $byDate = [];

        foreach ($limits as $limit) {
            $date = $limit->specific_date?->toDateString();

            if ($date !== null) {
                $byDate[$date] ??= $limit;
            }
        }

        return new Collection($byDate);
    }

    /**
     * @return Collection<int, Holiday>
     */
    private function holidaysOn(CarbonInterface $date): Collection
    {
        return $this->holidays->get($date->toDateString(), new Collection);
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

    private function scheduleMax(CarbonInterface $date): ?int
    {
        $schedule = $this->schedules->get($date->dayOfWeek);

        if (! $schedule instanceof BusinessSchedule || $schedule->max_orders <= 0) {
            return null;
        }

        return $schedule->max_orders;
    }
}
