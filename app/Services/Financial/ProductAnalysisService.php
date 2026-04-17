<?php

namespace App\Services\Financial;

use App\DataTransferObjects\Financial\ProductCostAnalysis;
use App\DataTransferObjects\Financial\ProductPortfolioSummary;
use App\Enums\Financial\MarginHealth;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Support\ProfitMargin;
use Illuminate\Support\Collection;

/**
 * Single-product and portfolio-wide cost/margin analysis.
 *
 * Delegates cost resolution to ProductCostResolver so analyze() and
 * portfolio() agree on how an individual product's cost is determined.
 */
class ProductAnalysisService
{
    public function __construct(
        private ProductCostResolver $costResolver,
    ) {}

    /**
     * Analyze a single product: cost, margin, and a suggested price.
     */
    public function analyze(Product $product, float $targetMarginPercent = 65.0): ProductCostAnalysis
    {
        $product->loadMissing('recipes');

        $cost = $this->costResolver->resolve($product);
        $price = (float) ($product->price ?? 0);
        $ingredients = $this->formatIngredients($product->recipes->first());

        $margin = $cost > 0 ? ProfitMargin::calculate($price, $cost) : null;

        $suggestedPrice = ($cost > 0 && $targetMarginPercent > 0)
            ? $cost / (1 - ($targetMarginPercent / 100))
            : 0.0;

        return new ProductCostAnalysis(
            cost: $cost,
            price: $price,
            suggestedPrice: $suggestedPrice,
            currentMarginPercent: $margin,
            profitPerUnit: $price - $cost,
            marginHealth: MarginHealth::fromPercentage($margin),
            ingredients: $ingredients,
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
                $cost = $this->costResolver->resolve($product);
                $price = (float) $product->price;
                $margin = $cost > 0 ? ProfitMargin::calculate($price, $cost) : null;
                $marginAmount = $margin !== null ? $price - $cost : null;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $price,
                    'cost' => $cost,
                    'margin_percentage' => $margin,
                    'margin_amount' => $marginAmount !== null ? round($marginAmount, 2) : null,
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
}
