<?php

namespace App\Observers\Financial;

use App\Models\Financial\Expense;
use App\ValueObjects\Money;

class ExpenseObserver
{
    public function saving(Expense $expense): void
    {
        $expense->deductible_amount = Money::fromDollars(
            round($expense->amount->dollars() * ($expense->business_percentage / 100), 2),
        );
    }
}
