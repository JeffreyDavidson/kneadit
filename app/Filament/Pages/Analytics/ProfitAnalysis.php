<?php

namespace App\Filament\Pages\Analytics;

use App\DataTransferObjects\Financial\ProductPortfolioSummary;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Financial\ProductAnalysisService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;

/**
 * @property-read ProductPortfolioSummary $portfolio
 */
class ProfitAnalysis extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    #[\Override]
    protected static ?string $navigationLabel = 'Profit Analysis';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    #[\Override]
    protected static ?int $navigationSort = 7;

    #[\Override]
    protected string $view = 'filament.pages.analytics.profit-analysis';

    public string $sortBy = 'margin_desc';

    public function updatedSortBy(): void
    {
        // Triggers re-render with the new sort order
    }

    #[Computed]
    public function portfolio(): ProductPortfolioSummary
    {
        return resolve(ProductAnalysisService::class)->portfolio($this->sortBy);
    }

    /**
     * @return array<string, mixed>
     */
    public function getOverallStats(): array
    {
        $portfolio = $this->portfolio;

        return [
            'total_products' => $portfolio->totalProducts,
            'products_with_costs' => $portfolio->productsWithCosts,
            'average_margin' => $portfolio->averageMargin ? round($portfolio->averageMargin, 1) : null,
            'products_missing_costs' => $portfolio->productsMissingCosts(),
            'margin_breakdown' => [
                'high' => $portfolio->marginBreakdown['high'],
                'medium' => $portfolio->marginBreakdown['medium'],
                'low' => $portfolio->marginBreakdown['low'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTotalRevenuePotential(): array
    {
        $portfolio = $this->portfolio;

        return [
            'total_revenue_potential' => $portfolio->totalRevenuePotential,
            'total_costs' => $portfolio->totalCosts,
            'total_profit_potential' => $portfolio->totalProfitPotential,
            'overall_margin' => round($portfolio->overallMarginPercent, 1),
        ];
    }

    /** @return Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> */
    public function getProductAnalysis(): Collection
    {
        return $this->portfolio->products;
    }

    /** @return Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> */
    public function getTopProfitableProducts(): Collection
    {
        return $this->portfolio->topProfitable();
    }

    /** @return Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> */
    public function getLowestMarginProducts(): Collection
    {
        return $this->portfolio->lowestMargin();
    }

    /** @return Collection<int, array{id: int, name: string, price: float, cost: float, margin_percentage: float|null, margin_amount: float|null, has_cost_data: bool, color_class: string}> */
    public function getMissingCostProducts(): Collection
    {
        return $this->portfolio->missingCosts();
    }
}
