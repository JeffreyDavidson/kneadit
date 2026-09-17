<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Production;

final readonly class PrepWeekSummary
{
    public function __construct(
        public int $totalOrders,
        public int $totalItems,
        public float $totalRevenue,
        public float $totalPrepHours,
    ) {}

    /** @return array{total_orders: int, total_items: int, total_revenue: float, total_prep_hours: float} */
    public function toArray(): array
    {
        return ['total_orders' => $this->totalOrders, 'total_items' => $this->totalItems, 'total_revenue' => $this->totalRevenue, 'total_prep_hours' => $this->totalPrepHours];
    }
}
