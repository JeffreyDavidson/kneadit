<?php

namespace App\Services;

use App\Models\BusinessSchedule;

class ScheduleService
{
    public function forDay(int $dayOfWeek): ?BusinessSchedule
    {
        return BusinessSchedule::query()->where('day_of_week', $dayOfWeek)->first();
    }

    public function isOpenOn(int $dayOfWeek): bool
    {
        $schedule = $this->forDay($dayOfWeek);

        return $schedule?->is_open ?? false;
    }
}
