<?php

namespace App\DataTransferObjects\Financial;

use App\ValueObjects\Money;

final readonly class FinancialReportMonth
{
    public function __construct(
        public string $month,
        public Money $revenue,
        public Money $expenses,
        public Money $profit,
    ) {}
}
