<?php

namespace App\Filament\Pages\Analytics;

use App\DataTransferObjects\Financial\ProductPortfolioSummary;
use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Financial\ProductFinancialService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
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
        // This will trigger a re-render with the new sort order
    }

    public function getPortfolioProperty(): ProductPortfolioSummary
    {
        return resolve(ProductFinancialService::class)->portfolio($this->sortBy);
    }
}
