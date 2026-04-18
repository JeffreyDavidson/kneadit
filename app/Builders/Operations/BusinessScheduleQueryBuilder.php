<?php

namespace App\Builders\Operations;

use App\Models\Operations\BusinessSchedule;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<BusinessSchedule> */
class BusinessScheduleQueryBuilder extends Builder
{
    public function forDay(int $dayOfWeek): static
    {
        $this->where('day_of_week', $dayOfWeek);

        return $this;
    }
}
