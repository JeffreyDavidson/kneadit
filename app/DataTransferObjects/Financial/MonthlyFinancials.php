<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Financial;

use App\ValueObjects\Money;

final readonly class MonthlyFinancials
{
    public function __construct(
        public int $month,
        public string $monthName,
        public Money $revenue,
        public Money $expenses,
        public Money $net,
    ) {}
}
