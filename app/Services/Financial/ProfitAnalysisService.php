<?php

namespace App\Services\Financial;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProfitAnalysisService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getProductAnalysis(string $sortBy = 'margin_desc'): Collection
    {
        $products = Product::with(['recipes'])
            ->where('is_active', true)
            ->get()
            ->map(function (Product $product) {
                $cost = $this->getProductCost($product);
                $price = $product->price;
                $margin = null;
                $marginAmount = null;

                if ($cost && $price) {
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
                    'has_cost_data' => $cost !== null && $cost > 0,
                    'color_class' => $this->getMarginColorClass($margin),
                ];
            });

        return $this->sortProducts($products, $sortBy);
    }

    public function getProductCost(Product $product): ?float
    {
        if ($product->cost && $product->cost > 0) {
            return $product->cost;
        }

        $recipe = $product->recipes->where('cost', '>', 0)->first();
        if ($recipe) {
            return $recipe->cost;
        }

        return null;
    }

    public function getMarginColorClass(?float $margin): string
    {
        if ($margin === null) {
            return 'gray';
        }

        if ($margin >= 50) {
            return 'green';
        }

        if ($margin >= 30) {
            return 'yellow';
        }

        return 'red';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $products
     * @return Collection<int, array<string, mixed>>
     */
    public function sortProducts(Collection $products, string $sortBy): Collection
    {
        return match ($sortBy) {
            'margin_asc' => $products->sortBy('margin_percentage'),
            'name_asc' => $products->sortBy('name'),
            'price_desc' => $products->sortByDesc('price'),
            'price_asc' => $products->sortBy('price'),
            default => $products->sortByDesc('margin_percentage'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function getOverallStats(string $sortBy = 'margin_desc'): array
    {
        $products = $this->getProductAnalysis($sortBy);
        $productsWithCostData = $products->where('has_cost_data', true);

        $totalProducts = $products->count();
        $productsWithCosts = $productsWithCostData->count();
        $averageMargin = $productsWithCostData->where('margin_percentage', '!=', null)->avg('margin_percentage');

        $marginBreakdown = [
            'high' => $productsWithCostData->where('color_class', 'green')->count(),
            'medium' => $productsWithCostData->where('color_class', 'yellow')->count(),
            'low' => $productsWithCostData->where('color_class', 'red')->count(),
        ];

        return [
            'total_products' => $totalProducts,
            'products_with_costs' => $productsWithCosts,
            'products_missing_costs' => $totalProducts - $productsWithCosts,
            'average_margin' => $averageMargin ? round($averageMargin, 1) : null,
            'margin_breakdown' => $marginBreakdown,
        ];
    }

    /**
     * @return Collection<int, mixed>
     */
    public function getTopProfitableProducts(string $sortBy = 'margin_desc'): Collection
    {
        return $this->getProductAnalysis($sortBy)
            ->where('has_cost_data', true)
            ->where('margin_amount', '>', 0)
            ->sortByDesc('margin_amount')
            ->take(5);
    }

    /**
     * @return Collection<int, mixed>
     */
    public function getLowestMarginProducts(string $sortBy = 'margin_desc'): Collection
    {
        return $this->getProductAnalysis($sortBy)
            ->where('has_cost_data', true)
            ->where('margin_percentage', '!=', null)
            ->sortBy('margin_percentage')
            ->take(5);
    }

    /**
     * @return Collection<int, mixed>
     */
    public function getMissingCostProducts(string $sortBy = 'margin_desc'): Collection
    {
        return $this->getProductAnalysis($sortBy)
            ->where('has_cost_data', false)
            ->sortBy('name');
    }

    /**
     * @return array<string, mixed>
     */
    public function getTotalRevenuePotential(string $sortBy = 'margin_desc'): array
    {
        $products = $this->getProductAnalysis($sortBy)->where('has_cost_data', true);

        $totalRevenue = $products->sum('price');
        $totalCosts = $products->sum('cost');
        $totalProfit = (float) $totalRevenue - (float) $totalCosts;
        $overallMargin = (float) $totalRevenue > 0 ? (($totalProfit / (float) $totalRevenue) * 100) : 0;

        return [
            'total_revenue_potential' => $totalRevenue,
            'total_costs' => $totalCosts,
            'total_profit_potential' => $totalProfit,
            'overall_margin' => round($overallMargin, 2),
        ];
    }
}
