<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Financial;

use App\ValueObjects\Money;
use Illuminate\Support\Collection;

final readonly class FinancialSummary
{
    /**
     * @param  Collection<int, MonthlyFinancials>  $monthlyBreakdown
     * @param  Collection<int, FinancialExpenseBreakdown>  $expenseBreakdown
     */
    public function __construct(
        public Money $totalRevenue,
        public Money $totalExpenses,
        public Money $netProfit,
        public Money $cogsAmount,
        public float $cogsPercentage,
        public Collection $monthlyBreakdown,
        public Collection $expenseBreakdown,
    ) {}
}
