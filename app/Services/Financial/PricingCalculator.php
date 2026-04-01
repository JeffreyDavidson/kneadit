<?php

namespace App\Services\Financial;

class PricingCalculator
{
    /**
     * Calculate recommended pricing for a product.
     *
     * @return array<string, mixed>
     */
    public function calculate(
        float $ingredientCost,
        int $prepTimeMinutes,
        float $hourlyLaborRate,
        float $overheadPercentage,
        int $targetProfitMargin,
        string $positioning = 'standard',
        ?float $currentPrice = null,
    ): array {
        $laborCost = ($prepTimeMinutes / 60) * $hourlyLaborRate;
        $baseCost = $ingredientCost + $laborCost;
        $overheadAmount = $baseCost * ($overheadPercentage / 100);
        $totalCost = $baseCost + $overheadAmount;

        $marginDecimal = $targetProfitMargin / 100;
        $recommendedPrice = $marginDecimal < 1 ? $totalCost / (1 - $marginDecimal) : $totalCost * 3;

        $multiplier = match ($positioning) {
            'economy' => 0.85,
            'premium' => 1.25,
            default => 1.0,
        };
        $recommendedPrice *= $multiplier;
        $recommendedPrice = $this->roundPrice($recommendedPrice);

        $minPrice = $this->roundPrice($totalCost * 1.15);
        $maxPrice = $this->roundPrice($totalCost / (1 - 0.70) * $multiplier);

        return [
            'ingredient_cost' => round($ingredientCost, 2),
            'labor_cost' => round($laborCost, 2),
            'overhead' => round($overheadAmount, 2),
            'total_cost' => round($totalCost, 2),
            'recommended_price' => $recommendedPrice,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'current_price' => $currentPrice,
            'profit_per_unit' => round($recommendedPrice - $totalCost, 2),
            'actual_margin' => $recommendedPrice > 0 ? round((($recommendedPrice - $totalCost) / $recommendedPrice) * 100, 1) : 0,
            'bulk' => [
                ['qty' => 6, 'label' => '6-pack', 'unit_price' => $this->roundPrice($recommendedPrice * 0.90), 'total' => round($recommendedPrice * 0.90 * 6, 2)],
                ['qty' => 12, 'label' => 'Dozen', 'unit_price' => $this->roundPrice($recommendedPrice * 0.85), 'total' => round($recommendedPrice * 0.85 * 12, 2)],
            ],
        ];
    }

    public function roundPrice(float $price): float
    {
        if ($price < 5) {
            return round(ceil($price * 4) / 4 - 0.01, 2);
        }

        return round(floor($price) + 0.99, 2);
    }
}
