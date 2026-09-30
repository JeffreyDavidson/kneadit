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

    public function onWeekday(DayOfWeek $day): static
    {
        $this->whereNull('specific_date')->where('day_of_week', $day->value);

        return $this;
    }
}
