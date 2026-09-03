<?php

namespace App\DataTransferObjects\Financial;

use App\ValueObjects\Money;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class FinancialReportResult implements Arrayable
{
    /**
     * @param list<array{month: string, revenue: Money, expenses: Money, profit: Money}> $monthly
     * @param list<array{category: string, amount: Money}> $expensesByCategory
     */
    public function __construct(
        public Money $totalRevenue,
        public Money $totalExpenses,
        public Money $profit,
        public Money $deductible,
        public array $monthly,
        public array $expensesByCategory,
    ) {}

    /**
     * @return array{
     *     totalRevenue: float,
     *     totalExpenses: float,
     *     profit: float,
     *     deductible: float,
     *     monthly: list<array{month: string, revenue: float, expenses: float, profit: float}>,
     *     expensesByCategory: list<array{category: string, amount: float}>
     * }
     */
    public function toArray(): array
    {
        return [
            'totalRevenue' => $this->totalRevenue->dollars(),
            'totalExpenses' => $this->totalExpenses->dollars(),
            'profit' => $this->profit->dollars(),
            'deductible' => $this->deductible->dollars(),
            'monthly' => array_map(static fn (array $month): array => [
                'month' => $month['month'],
                'revenue' => $month['revenue']->dollars(),
                'expenses' => $month['expenses']->dollars(),
                'profit' => $month['profit']->dollars(),
            ], $this->monthly),
            'expensesByCategory' => array_map(static fn (array $category): array => [
                'category' => $category['category'],
                'amount' => $category['amount']->dollars(),
            ], $this->expensesByCategory),
        ];
    }
}
