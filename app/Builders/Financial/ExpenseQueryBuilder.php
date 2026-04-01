<?php

namespace App\Builders\Financial;

use App\Enums\Financial\ExpenseCategory;
use App\Models\Financial\Expense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/** @extends Builder<Expense> */
class ExpenseQueryBuilder extends Builder
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

    public function cogs(): static
    {
        $this->whereIn('category', [ExpenseCategory::Ingredients, ExpenseCategory::Packaging]);

        return $this;
    }

    public function byCategory(): static
    {
        $this->select('category', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category');

        return $this;
    }
}
