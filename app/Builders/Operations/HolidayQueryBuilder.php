<?php

namespace App\Builders\Operations;

use App\Models\Operations\Holiday;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

/** @extends Builder<Holiday> */
class HolidayQueryBuilder extends Builder
{
    public function upcoming(): static
    {
        $this->where('date', '>=', Date::today())->orderBy('date');

        return $this;
    }

    public function active(): static
    {
        $this->where('is_active', true);

        return $this;
    }
}
