<?php

namespace App\DataTransferObjects\Inventory;

use App\ValueObjects\Money;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class ProductReportResult implements Arrayable
{
    /** @param list<array{name: string, price: Money, cost: Money, units_sold: int, revenue: Money, margin: float|null}> $products */
    public function __construct(
        public array $products,
    ) {}

    /**
     * @return array{
     *     products: list<array{name: string, price: float, cost: float, units_sold: int, revenue: float, margin: float|null}>
     * }
     */
    public function toArray(): array
    {
        return [
            'products' => array_map(static fn (array $product): array => [
                'name' => $product['name'],
                'price' => $product['price']->dollars(),
                'cost' => $product['cost']->dollars(),
                'units_sold' => $product['units_sold'],
                'revenue' => $product['revenue']->dollars(),
                'margin' => $product['margin'],
            ], $this->products),
        ];
    }
}
