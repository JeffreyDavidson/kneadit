<?php

namespace App\Filament\Pages\Analytics;

use App\DataTransferObjects\Financial\ProductPortfolioSummary;
use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Financial\ProductFinancialService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class ProfitAnalysis extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Profit Analysis';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.analytics.profit-analysis';

    public string $sortBy = 'margin_desc';

    public function updatedSortBy(): void
    {
        // Triggers re-render with the new sort order
    }

    public function getPortfolioProperty(): ProductPortfolioSummary
    {
        return resolve(ProductFinancialService::class)->portfolio($this->sortBy);
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
                'high' => $portfolio->marginBreakdown['high'] ?? 0,
                'medium' => $portfolio->marginBreakdown['medium'] ?? 0,
                'low' => $portfolio->marginBreakdown['low'] ?? 0,
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

    /** @return Collection<int, array<string, mixed>> */
    public function getProductAnalysis(): Collection
    {
        return $this->portfolio->products;
    }

    /** @return Collection<int, array<string, mixed>> */
    public function getTopProfitableProducts(): Collection
    {
        return $this->portfolio->topProfitable();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function getLowestMarginProducts(): Collection
    {
        return $this->portfolio->lowestMargin();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function getMissingCostProducts(): Collection
    {
        return $this->portfolio->missingCosts();
    }
}
