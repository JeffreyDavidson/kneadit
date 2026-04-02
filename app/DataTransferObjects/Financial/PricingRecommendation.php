<?php

namespace App\DataTransferObjects\Financial;

final readonly class PricingRecommendation
{
    /**
     * @param array<int, array{qty: int, label: string, unit_price: float, total: float}> $bulkTiers
     */
    public function __construct(
        public float $ingredientCost,
        public float $laborCost,
        public float $overhead,
        public float $totalCost,
        public float $recommendedPrice,
        public float $minPrice,
        public float $maxPrice,
        public ?float $currentPrice,
        public float $profitPerUnit,
        public float $actualMarginPercent,
        public array $bulkTiers,
    ) {}
}
