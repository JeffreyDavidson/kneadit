<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Orders;

use App\ValueObjects\Money;

final readonly class SalesReportProduct
{
    public function __construct(
        public string $name,
        public int $unitsSold,
        public Money $revenue,
    ) {}

    /** @return array{name: string, units_sold: int, revenue: float} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'units_sold' => $this->unitsSold,
            'revenue' => $this->revenue->dollars(),
        ];
    }
}
