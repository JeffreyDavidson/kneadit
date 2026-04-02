<?php

namespace App\DataTransferObjects\Financial;

use App\Enums\Financial\MarginHealth;
use Illuminate\Support\Collection;

final readonly class ProductCostAnalysis
{
    /**
     * @param Collection<int, array{name: string, quantity: float, unit: string, cost_per_unit: float, total_cost: float}> $ingredients
     */
    public function __construct(
        public float $cost,
        public float $price,
        public float $suggestedPrice,
        public ?float $currentMarginPercent,
        public float $profitPerUnit,
        public MarginHealth $marginHealth,
        public Collection $ingredients,
    ) {}
}
