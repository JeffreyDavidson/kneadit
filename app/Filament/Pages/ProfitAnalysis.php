<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Financial\ProfitAnalysisService;
use Filament\Pages\Page;
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

    /** @return Collection<int, mixed> */
    public function getProductAnalysis(): Collection
    {
        return resolve(ProfitAnalysisService::class)->getProductAnalysis($this->sortBy);
    }

    /** @return array<string, mixed> */
    public function getOverallStats(): array
    {
        return resolve(ProfitAnalysisService::class)->getOverallStats($this->sortBy);
    }

    /** @return Collection<int, mixed> */
    public function getTopProfitableProducts(): Collection
    {
        return resolve(ProfitAnalysisService::class)->getTopProfitableProducts($this->sortBy);
    }

    /** @return Collection<int, mixed> */
    public function getLowestMarginProducts(): Collection
    {
        return resolve(ProfitAnalysisService::class)->getLowestMarginProducts($this->sortBy);
    }

    /** @return Collection<int, mixed> */
    public function getMissingCostProducts(): Collection
    {
        return resolve(ProfitAnalysisService::class)->getMissingCostProducts($this->sortBy);
    }

    /** @return array<string, mixed> */
    public function getTotalRevenuePotential(): array
    {
        return resolve(ProfitAnalysisService::class)->getTotalRevenuePotential($this->sortBy);
    }
}
