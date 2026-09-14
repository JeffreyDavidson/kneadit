<?php

namespace App\DataTransferObjects\Platform;

final readonly class TenantLeaderboardEntry
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
        public string $owner,
        public string $email,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'plan' => $this->plan,
            'total_orders' => $this->totalOrders,
            'month_orders' => $this->monthOrders,
            'total_products' => $this->totalProducts,
            'total_categories' => $this->totalCategories,
            'avg_review' => $this->avgReview,
            'owner' => $this->owner,
            'email' => $this->email,
        ];
    }
}
