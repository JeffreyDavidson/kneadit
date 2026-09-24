<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Inventory;

use App\ValueObjects\Money;

final readonly class ProductReportProduct
{
    public function __construct(
        public string $name,
        public Money $price,
        public Money $cost,
        public int $unitsSold,
        public Money $revenue,
        public ?float $margin,
    ) {}

    /** @return array{name: string, price: float, cost: float, units_sold: int, revenue: float, margin: float|null} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price->dollars(),
            'cost' => $this->cost->dollars(),
            'units_sold' => $this->unitsSold,
            'revenue' => $this->revenue->dollars(),
            'margin' => $this->margin,
        ];
    }
}
