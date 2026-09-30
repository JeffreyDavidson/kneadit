<?php

declare(strict_types=1);

namespace App\Builders\Operations;

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
