<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Orders;

use App\ValueObjects\Money;

final readonly class SalesReportDay
{
    public function __construct(
        public string $date,
        public Money $revenue,
    ) {}

    /** @return array{date: string, revenue: float} */
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'revenue' => $this->revenue->dollars(),
        ];
    }
}
