<?php

namespace App\Observers;

use App\Models\Expense;

class ExpenseObserver
{
    public function saving(Expense $expense): void
    {
        $expense->deductible_amount = round((float) $expense->amount * ($expense->business_percentage / 100), 2);
    }
}
