<?php

namespace App\DataTransferObjects\Platform;

final readonly class TenantMetrics
{
    public function __construct(
        public string $id,
        public string $name,
        public string $plan,
        public int $totalOrders,
        public int $monthOrders,
        public int $totalProducts,
        public int $totalCategories,
        public float $avgReview,
    ) {}
}
