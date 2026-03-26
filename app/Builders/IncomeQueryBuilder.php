<?php

namespace App\Builders;

use App\Models\Income;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Income> */
class IncomeQueryBuilder extends Builder
{
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
