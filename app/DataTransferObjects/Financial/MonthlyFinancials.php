<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Financial;

final readonly class MonthlyFinancials
{
    public function __construct(
        public int $month,
        public string $monthName,
        public float $revenue,
        public float $expenses,
        public float $net,
    ) {}
}
