<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Platform\SubscriptionTier;
use App\Enums\Staff\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Analytics\ReviewAnalyticsService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class ReviewAnalytics extends Page
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

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Review Analytics';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.analytics.review-analytics';

    private function service(): ReviewAnalyticsService
    {
        return resolve(ReviewAnalyticsService::class);
    }

    /** @return array<string, mixed> */
    public function getOverallStats(): array
    {
        return $this->service()->getOverallStats();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRatingDistribution(): array
    {
        return $this->service()->getRatingDistribution();
    }

    /** @return array<int, array<string, mixed>> */
    public function getMonthlyTrend(): array
    {
        return $this->service()->getMonthlyTrend();
    }

    /** @return Collection<int, mixed> */
    public function getTopReviewedProducts(): Collection
    {
        return $this->service()->getTopReviewedProducts();
    }

    /** @return Collection<int, mixed> */
    public function getRecentReviews(): Collection
    {
        return $this->service()->getRecentReviews();
    }

    /** @return array<string, float> */
    public function getSentimentAnalysis(): array
    {
        return $this->service()->getSentimentAnalysis();
    }
}
