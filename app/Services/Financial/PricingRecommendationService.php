<?php

namespace App\Services\Financial;

use App\DataTransferObjects\Financial\PricingRecommendation;
use App\Enums\Financial\PricingPosition;

/**
 * Produces pricing recommendations from raw inputs (cost, labor, overhead, margin).
 *
 * Used by the pricing-engine tooling and the quick price-suggestion tool; neither
 * needs a full product record. Keep this class free of Eloquent queries.
 */
class PricingRecommendationService
{
    public function recommend(
        float $ingredientCost,
        int $prepTimeMinutes,
        float $hourlyLaborRate,
        float $overheadPercentage,
        int $targetMarginPercent,
        PricingPosition $positioning = PricingPosition::Standard,
        ?float $currentPrice = null,
    ): PricingRecommendation {
        $laborCost = ($prepTimeMinutes / 60) * $hourlyLaborRate;
        $baseCost = $ingredientCost + $laborCost;
        $overheadAmount = $baseCost * ($overheadPercentage / 100);
        $totalCost = $baseCost + $overheadAmount;

        $marginDecimal = $targetMarginPercent / 100;
        $recommendedPrice = $marginDecimal < 1
            ? $totalCost / (1 - $marginDecimal)
            : $totalCost * 3;

        $recommendedPrice *= $positioning->multiplier();
        $recommendedPrice = $this->roundPrice($recommendedPrice);

        $minPrice = $this->roundPrice($totalCost * 1.15);
        $maxPrice = $this->roundPrice($totalCost / (1 - 0.70) * $positioning->multiplier());

        return new PricingRecommendation(
            ingredientCost: round($ingredientCost, 2),
            laborCost: round($laborCost, 2),
            overhead: round($overheadAmount, 2),
            totalCost: round($totalCost, 2),
            recommendedPrice: $recommendedPrice,
            minPrice: $minPrice,
            maxPrice: $maxPrice,
            currentPrice: $currentPrice,
            profitPerUnit: round($recommendedPrice - $totalCost, 2),
            actualMarginPercent: $recommendedPrice > 0
                ? round((($recommendedPrice - $totalCost) / $recommendedPrice) * 100, 1)
                : 0,
            bulkTiers: [
                ['qty' => 6, 'label' => '6-pack', 'unit_price' => $this->roundPrice($recommendedPrice * 0.90), 'total' => round($recommendedPrice * 0.90 * 6, 2)],
                ['qty' => 12, 'label' => 'Dozen', 'unit_price' => $this->roundPrice($recommendedPrice * 0.85), 'total' => round($recommendedPrice * 0.85 * 12, 2)],
            ],
        );
    }

    public function suggestPrice(float $cost, float $targetMarginPercent): float
    {
        if ($cost <= 0 || $targetMarginPercent <= 0) {
            return 0.0;
        }

        return $cost / (1 - ($targetMarginPercent / 100));
    }

    private function roundPrice(float $price): float
    {
        if ($price < 5) {
            return round(ceil($price * 4) / 4 - 0.01, 2);
        }

        return round(floor($price) + 0.99, 2);
    }
}
