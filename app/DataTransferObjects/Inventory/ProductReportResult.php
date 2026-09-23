<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Inventory;

use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, mixed> */
final readonly class ProductReportResult implements Arrayable
{
    /** @param list<ProductReportProduct> $products */
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
            'products' => array_map(
                static fn (ProductReportProduct $product): array => $product->toArray(),
                $this->products,
            ),
        ];
    }
}
