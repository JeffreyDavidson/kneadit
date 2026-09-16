<?php

namespace App\DataTransferObjects\Platform;

final readonly class TenantLeaderboardSummary
{
    public function __construct(
        public int $totalOrders,
        public int $totalBakeries,
        public int $activeBakeries,
        public float $averageOrdersActive,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'total_orders' => $this->totalOrders,
            'total_bakeries' => $this->totalBakeries,
            'active_bakeries' => $this->activeBakeries,
            'avg_orders_active' => $this->averageOrdersActive === 0.0 ? 0 : $this->averageOrdersActive,
        ];
    }
}
