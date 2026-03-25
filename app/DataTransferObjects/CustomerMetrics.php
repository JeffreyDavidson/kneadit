<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Carbon;

final readonly class CustomerMetrics
{
    public function __construct(
        public float $lifetimeValue,
        public int $orderCount,
        public float $averageOrderValue,
        public ?Carbon $lastOrderDate,
        public ?int $daysSinceLastOrder,
        public bool $isAtRisk,
        public int $totalPoints,
        public int $lifetimePointsEarned,
    ) {}
}
