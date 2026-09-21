<?php

namespace App\Filament\Pages\Analytics;

use App\DataTransferObjects\Analytics\RatingDistributionEntry;
use App\DataTransferObjects\Analytics\RecentReview;
use App\DataTransferObjects\Analytics\ReviewMonthlyTrend;
use App\DataTransferObjects\Analytics\TopReviewedProduct;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Analytics\ReviewAnalyticsService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Laravel\Pennant\Feature;

class ReviewAnalytics extends Page
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
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    #[\Override]
    protected static ?string $navigationLabel = 'Review Analytics';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 4;

    #[\Override]
    protected string $view = 'filament.pages.analytics.review-analytics';

    private function service(): ReviewAnalyticsService
    {
        return resolve(ReviewAnalyticsService::class);
    }

    /** @return array{total_reviews: int, average_rating: float, approval_rate: float, approved_reviews: int} */
    public function getOverallStats(): array
    {
        return $this->service()->getOverallStats()->toArray();
    }

    /** @return list<array{rating: int, count: int, percentage: float}> */
    public function getRatingDistribution(): array
    {
        return array_map(
            static fn (RatingDistributionEntry $entry): array => $entry->toArray(),
            $this->service()->getRatingDistribution(),
        );
    }

    /** @return list<array{month: string, month_key: string, count: int, avg_rating: float}> */
    public function getMonthlyTrend(): array
    {
        return array_map(
            static fn (ReviewMonthlyTrend $trend): array => $trend->toArray(),
            $this->service()->getMonthlyTrend(),
        );
    }

    /** @return Collection<int, array{id: int, name: string, reviews_count: int|null, average_rating: float}> */
    public function getTopReviewedProducts(): Collection
    {
        return $this->service()->getTopReviewedProducts()->map(
            static fn (TopReviewedProduct $product): array => $product->toArray(),
        );
    }

    /** @return Collection<int, array{id: int, customer_name: string, product_name: string, rating: int, comment: ?string, is_approved: bool, is_featured: bool, created_at: ?Carbon}> */
    public function getRecentReviews(): Collection
    {
        return $this->service()->getRecentReviews()->map(
            static fn (RecentReview $review): array => $review->toArray(),
        );
    }

    /** @return array{positive: float, neutral: float, negative: float} */
    public function getSentimentAnalysis(): array
    {
        return $this->service()->getSentimentAnalysis()->toArray();
    }
}
