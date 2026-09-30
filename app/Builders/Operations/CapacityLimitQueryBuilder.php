<?php

declare(strict_types=1);

namespace App\Builders\Operations;

use App\Enums\Staff\DayOfWeek;
use App\Models\Operations\CapacityLimit;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<CapacityLimit> */
class CapacityLimitQueryBuilder extends Builder
{
    public function onSpecificDate(CarbonInterface $date): static
    {
        $this->whereDate('specific_date', $date);

        return $this;
    }

    /**
     * Weekday limits in week order (Monday first, per DayOfWeek), then
     * specific dates by date. Descending reverses the whole list.
     */
    public function orderByDay(string $direction = 'asc'): static
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        $weekdays = array_map(fn (DayOfWeek $day): string => $day->value, DayOfWeek::cases());
        $whens = implode(' ', array_map(fn (int $position): string => "when ? then {$position}", array_keys($weekdays)));
        $unknown = count($weekdays);

        $this->orderByRaw("specific_date is not null {$direction}")
            ->orderByRaw("case day_of_week {$whens} else {$unknown} end {$direction}", $weekdays)
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
