<?php

namespace App\Services\Financial;

use App\DataTransferObjects\Financial\PricingRecommendation;
use App\DataTransferObjects\Financial\ProductCostAnalysis;
use App\DataTransferObjects\Financial\ProductPortfolioSummary;
use App\Enums\Financial\MarginHealth;
use App\Enums\Financial\PricingPosition;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use Illuminate\Support\Collection;

class ProductFinancialService
{
    /**
     * Analyze a product's cost, margin, and suggested price.
     * Answers: "What does it cost, what margin am I making, what should I charge?"
     */
    public function analyze(Product $product, float $targetMarginPercent = 65.0): ProductCostAnalysis
    {
        $product->loadMissing('recipes');

        $cost = $this->resolveCost($product);
        $price = (float) ($product->price ?? 0);
        $ingredients = $this->formatIngredients($product->recipes->first());

        $margin = ($cost > 0 && $price > 0)
            ? (($price - $cost) / $price) * 100
            : null;

        $suggestedPrice = ($cost > 0 && $targetMarginPercent > 0)
            ? $cost / (1 - ($targetMarginPercent / 100))
            : 0.0;

        return new ProductCostAnalysis(
            cost: $cost,
            price: $price,
            suggestedPrice: $suggestedPrice,
            currentMarginPercent: $margin ? round($margin, 2) : null,
            profitPerUnit: $price - $cost,
            marginHealth: MarginHealth::fromPercentage($margin),
            ingredients: $ingredients,
        );
    }

    /**
     * Full pricing recommendation with labor, overhead, positioning, and bulk tiers.
     */
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

    /**
     * Portfolio-wide analysis: all active products with margins, stats, and rankings.
     */
    public function portfolio(string $sortBy = 'margin_desc'): ProductPortfolioSummary
    {
        $products = once(fn () => $this->loadProductAnalysis());
        $sorted = $this->sortProducts($products, $sortBy);

        $productsWithCostData = $sorted->where('has_cost_data', true);
        $totalProducts = $sorted->count();
        $productsWithCosts = $productsWithCostData->count();
        $averageMargin = $productsWithCostData->whereNotNull('margin_percentage')->avg('margin_percentage');

        $totalRevenue = $productsWithCostData->sum('price');
        $totalCosts = $productsWithCostData->sum('cost');
        $totalProfit = $totalRevenue - $totalCosts;
        $overallMargin = $totalRevenue > 0 ? (($totalProfit / $totalRevenue) * 100) : 0;

        return new ProductPortfolioSummary(
            products: $sorted,
            totalProducts: $totalProducts,
            productsWithCosts: $productsWithCosts,
            averageMargin: $averageMargin ? round($averageMargin, 1) : null,
            marginBreakdown: [
                'high' => $productsWithCostData->where('color_class', MarginHealth::Healthy->cssClass())->count(),
                'medium' => $productsWithCostData->where('color_class', MarginHealth::Warning->cssClass())->count(),
                'low' => $productsWithCostData->where('color_class', MarginHealth::Critical->cssClass())->count(),
            ],
            totalRevenuePotential: $totalRevenue,
            totalCosts: $totalCosts,
            totalProfitPotential: $totalProfit,
            overallMarginPercent: round($overallMargin, 2),
        );
    }

    /**
     * Simple suggested price from cost and target margin.
     */
    public function suggestPrice(float $cost, float $targetMarginPercent): float
    {
        if ($cost <= 0 || $targetMarginPercent <= 0) {
            return 0.0;
        }

        return $cost / (1 - ($targetMarginPercent / 100));
    }

    /**
     * Resolve the best available cost for a product.
     * Priority: Product.cost > Recipe.cost > calculated from ingredients.
     */
    private function resolveCost(Product $product): float
    {
        if ($product->cost && $product->cost > 0) {
            return (float) $product->cost;
        }

        $recipe = $product->recipes->where('cost', '>', 0)->first();
        if ($recipe) {
            return (float) $recipe->cost;
        }

        // Fall back to calculating from ingredients JSON
        $recipe = $product->recipes->first();
        if ($recipe) {
            return $this->calculateRecipeCost($recipe);
        }

        return 0.0;
    }

    private function calculateRecipeCost(Recipe $recipe): float
    {
        if (! $recipe->ingredients) {
            return 0.0;
        }

        $totalCost = 0.0;

        foreach ($recipe->ingredients as $ingredient) {
            $cost = $ingredient['cost'] ?? 0;
            $quantity = $ingredient['quantity'] ?? 0;
            $totalCost += ($cost * $quantity);
        }

        return $totalCost;
    }

    /**
     * @return Collection<int, array{name: string, quantity: float, unit: string, cost_per_unit: float, total_cost: float}>
     */
    private function formatIngredients(?Recipe $recipe): Collection
    {
        if (! $recipe || ! $recipe->ingredients) {
            return collect();
        }

        return collect($recipe->ingredients)->map(function (array $ingredient) {
            $cost = $ingredient['cost'] ?? 0;
            $quantity = $ingredient['quantity'] ?? 0;

            return [
                'name' => $ingredient['name'] ?? '',
                'quantity' => $quantity,
                'unit' => $ingredient['unit'] ?? '',
                'cost_per_unit' => $cost,
                'total_cost' => $cost * $quantity,
            ];
        });
    }

    /** @return Collection<int, array<string, mixed>> */
    private function loadProductAnalysis(): Collection
    {
        return Product::with(['recipes'])
            ->where('is_active', true)
            ->get()
            ->map(function (Product $product) {
                $cost = $this->resolveCost($product);
                $price = (float) $product->price;
                $margin = null;
                $marginAmount = null;

                if ($cost > 0 && $price > 0) {
                    $margin = (($price - $cost) / $price) * 100;
                    $marginAmount = $price - $cost;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $price,
                    'cost' => $cost,
                    'margin_percentage' => $margin ? round($margin, 2) : null,
                    'margin_amount' => $marginAmount ? round($marginAmount, 2) : null,
                    'has_cost_data' => $cost > 0,
                    'color_class' => MarginHealth::fromPercentage($margin)->cssClass(),
                ];
            });
    }

    /**
     * @param Collection<int, array<string, mixed>> $products
     * @return Collection<int, array<string, mixed>>
     */
    private function sortProducts(Collection $products, string $sortBy): Collection
    {
        return match ($sortBy) {
            'margin_asc' => $products->sortBy('margin_percentage'),
            'name_asc' => $products->sortBy('name'),
            'price_desc' => $products->sortByDesc('price'),
            'price_asc' => $products->sortBy('price'),
            default => $products->sortByDesc('margin_percentage'),
        };
    }

    private function roundPrice(float $price): float
    {
        if ($price < 5) {
            return round(ceil($price * 4) / 4 - 0.01, 2);
        }

        return round(floor($price) + 0.99, 2);
    }
}
