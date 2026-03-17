<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\Product;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ProfitAnalysis extends Page
{
    use HasPlanGating, RequiresRole;

    protected static string $requiredPlan = 'pro';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Profit Analysis';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.profit-analysis';

    public string $sortBy = 'margin_desc';

    public function updatedSortBy(): void
    {
        // This will trigger a re-render with the new sort order
    }

    public function getProductAnalysis(): Collection
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

        return $this->sortProducts($products);
    }

    private function getProductCost(Product $product): ?float
    {
        // Check if product has a direct cost
        if ($product->cost && $product->cost > 0) {
            return $product->cost;
        }

        // Check if product has recipes with cost data
        $recipe = $product->recipes->where('cost', '>', 0)->first();
        if ($recipe) {
            return $recipe->cost;
        }

        return null;
    }

    private function getMarginColorClass(?float $margin): string
    {
        if ($margin === null) {
            return 'gray';
        }

        if ($margin >= 50) {
            return 'green';
        } elseif ($margin >= 30) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    private function sortProducts(Collection $products): Collection
    {
        switch ($this->sortBy) {
            case 'margin_desc':
                return $products->sortByDesc('margin_percentage');
            case 'margin_asc':
                return $products->sortBy('margin_percentage');
            case 'name_asc':
                return $products->sortBy('name');
            case 'price_desc':
                return $products->sortByDesc('price');
            case 'price_asc':
                return $products->sortBy('price');
            default:
                return $products->sortByDesc('margin_percentage');
        }
    }

    public function getOverallStats(): array
    {
        $products = $this->getProductAnalysis();
        $productsWithCostData = $products->where('has_cost_data', true);

        $totalProducts = $products->count();
        $productsWithCosts = $productsWithCostData->count();
        $averageMargin = $productsWithCostData->where('margin_percentage', '!=', null)->avg('margin_percentage');

        $marginBreakdown = [
            'high' => $productsWithCostData->where('color_class', 'green')->count(), // >= 50%
            'medium' => $productsWithCostData->where('color_class', 'yellow')->count(), // 30-49%
            'low' => $productsWithCostData->where('color_class', 'red')->count(), // < 30%
        ];

        return [
            'total_products' => $totalProducts,
            'products_with_costs' => $productsWithCosts,
            'products_missing_costs' => $totalProducts - $productsWithCosts,
            'average_margin' => $averageMargin ? round($averageMargin, 1) : null,
            'margin_breakdown' => $marginBreakdown,
        ];
    }

    public function getTopProfitableProducts(): Collection
    {
        return $this->getProductAnalysis()
            ->where('has_cost_data', true)
            ->where('margin_amount', '>', 0)
            ->sortByDesc('margin_amount')
            ->take(5);
    }

    public function getLowestMarginProducts(): Collection
    {
        return $this->getProductAnalysis()
            ->where('has_cost_data', true)
            ->where('margin_percentage', '!=', null)
            ->sortBy('margin_percentage')
            ->take(5);
    }

    public function getMissingCostProducts(): Collection
    {
        return $this->getProductAnalysis()
            ->where('has_cost_data', false)
            ->sortBy('name');
    }

    public function getTotalRevenuePotential(): array
    {
        $products = $this->getProductAnalysis()->where('has_cost_data', true);

        $totalRevenue = $products->sum('price');
        $totalCosts = $products->sum('cost');
        $totalProfit = $totalRevenue - $totalCosts;
        $overallMargin = $totalRevenue > 0 ? (($totalProfit / $totalRevenue) * 100) : 0;

        return [
            'total_revenue_potential' => $totalRevenue,
            'total_costs' => $totalCosts,
            'total_profit_potential' => $totalProfit,
            'overall_margin' => round($overallMargin, 2),
        ];
    }
}
