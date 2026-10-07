<?php

declare(strict_types=1);

namespace App\Builders\Financial;

use App\Models\Financial\Income;
use App\ValueObjects\DateRange;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Income> */
class IncomeQueryBuilder extends Builder
{
    /** Whole days: `date` is stored with a midnight time, so compare the date part. */
    public function inDateRange(DateRange $range): static
    {
        $this->whereDate('date', '>=', $range->start->toDateString())
            ->whereDate('date', '<=', $range->end->toDateString());

        return $this;
    }

    public function forYear(int $year): static
    {
        $this->whereYear('date', $year);

        return $this;
    }

    public function forMonth(int $year, int $month): static
    {
        $this->whereYear('date', $year)->whereMonth('date', $month);

        return $this;
    }
}
