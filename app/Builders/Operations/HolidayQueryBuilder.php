<?php

declare(strict_types=1);

namespace App\Builders\Operations;

use App\Models\Operations\Holiday;
use App\Services\Scheduling\BakeryClock;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Holiday> */
class HolidayQueryBuilder extends Builder
{
    public function upcoming(): static
    {
        $this->whereDate('date', '>=', resolve(BakeryClock::class)->today())->orderBy('date');

        return $this;
    }

    public function active(): static
    {
        $this->where('is_active', true);

        return $this;
    }

    public function betweenDates(CarbonInterface $start, CarbonInterface $end): static
    {
        $this->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);

        return $this;
    }
}
