<?php

declare(strict_types=1);

namespace App\Builders\Operations;

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\CapacityLimit;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

/** @extends Builder<CapacityLimit> */
class CapacityLimitQueryBuilder extends Builder
{
    /**
     * Position of each of the seven DayOfWeek values, bound in order; any
     * other stored value sorts after them.
     */
    private const string WEEKDAY_POSITION = 'case day_of_week when ? then 0 when ? then 1 when ? then 2 when ? then 3 when ? then 4 when ? then 5 when ? then 6 else 7 end';

    public function onSpecificDate(CarbonInterface $date): static
    {
        $this->whereDate('specific_date', $date);

        return $this;
    }

    /**
     * Weekday limits in week order (Monday first, per DayOfWeek), then
     * specific dates by date. Descending reverses both.
     */
    public function orderByDay(string $direction = 'asc'): static
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $weekdays = array_map(fn (DayOfWeek $day): string => $day->value, DayOfWeek::cases());

        $this->orderBy(new Expression('specific_date is not null'), $direction)
            ->orderByRaw(self::WEEKDAY_POSITION, $direction === 'desc' ? array_reverse($weekdays) : $weekdays)
            ->orderBy('specific_date', $direction);

        return $this;
    }

    /**
     * Limits for specific dates within the range, plus every weekday limit.
     */
    public function applyingBetween(CarbonInterface $start, CarbonInterface $end): static
    {
        $this->where(function (Builder $q) use ($start, $end): void {
            $q->whereNull('specific_date')->orWhere(function (Builder $q) use ($start, $end): void {
                $q->whereDate('specific_date', '>=', $start)->whereDate('specific_date', '<=', $end);
            });
        });

        return $this;
    }
}
