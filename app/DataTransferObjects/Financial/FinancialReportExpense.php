<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Financial;

use App\ValueObjects\Money;

final readonly class FinancialReportExpense
{
    public function __construct(
        public string $category,
        public Money $amount,
    ) {}
}
