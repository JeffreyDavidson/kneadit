<?php

namespace App\DataTransferObjects\Platform;

final readonly class TenantComparisonResult
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
        public int $daysSinceSignup,
        public int $setupCompleted,
        public int $healthScore,
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
            'days_since_signup' => $this->daysSinceSignup,
            'setup_completed' => $this->setupCompleted,
            'health_score' => $this->healthScore,
        ];
    }
}
