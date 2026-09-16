<?php

namespace App\DataTransferObjects\Production;

final readonly class ProductPreparationSummary
{
    public function __construct(
        public string $productName,
        public int $totalQuantity,
        public int $ordersCount,
    ) {}

    /** @return array{product_name: string, total_quantity: int, orders_count: int} */
    public function toArray(): array
    {
        return ['product_name' => $this->productName, 'total_quantity' => $this->totalQuantity, 'orders_count' => $this->ordersCount];
    }
}
