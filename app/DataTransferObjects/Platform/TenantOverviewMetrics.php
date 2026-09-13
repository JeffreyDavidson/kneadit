<?php

namespace App\DataTransferObjects\Platform;

final readonly class TenantOverviewMetrics
{
    public function __construct(
        public int $products,
        public int $categories,
        public int $orders,
        public int $customers,
        public int $reviews,
        public float $revenue,
        public ?string $lastOrder,
    ) {}
}
