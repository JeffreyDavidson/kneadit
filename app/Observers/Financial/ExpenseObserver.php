<?php

namespace App\Observers\Financial;

use App\Models\Financial\Expense;

class ExpenseObserver
{
    public function saving(Expense $expense): void
    {
        $expense->deductible_amount = $expense->business_percentage->applyTo($expense->amount);
    }
}
