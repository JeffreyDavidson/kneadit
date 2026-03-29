<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Collection;

final readonly class FinancialSummary
{
    /**
     * @param Collection<int, MonthlyFinancials> $monthlyBreakdown
     * @param Collection<int, array{category: string, amount: float|null, percentage: float}> $expenseBreakdown
     */
    public function __construct(
        public float $totalRevenue,
        public float $totalExpenses,
        public float $netProfit,
        public float $cogsAmount,
        public float $cogsPercentage,
        public Collection $monthlyBreakdown,
        public Collection $expenseBreakdown,
    ) {}
}
