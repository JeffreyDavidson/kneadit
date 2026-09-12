<?php

namespace App\DataTransferObjects\Customers;

use App\ValueObjects\Money;
use Illuminate\Support\Carbon;

final readonly class CustomerMetrics
{
    public function __construct(
        public Money $lifetimeValue,
        public int $orderCount,
        public Money $averageOrderValue,
        public ?Carbon $lastOrderDate,
        public ?int $daysSinceLastOrder,
        public bool $isAtRisk,
        public int $totalPoints,
        public int $lifetimePointsEarned,
    ) {}
}
