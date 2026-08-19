<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Engagement\Review;
use App\Queries\Engagement\ReviewSummaryQuery;
use Filament\Widgets\Widget;

class ReviewSummaryWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 19;

    protected string $view = 'filament.widgets.review-summary-widget';

    public function getAverageRating(): float
    {
        return $this->cached('avg', [3600, 7200], fn (): float => $this->query()->averageRating());
    }

    public function getTotalReviews(): int
    {
        return $this->cached('total', [3600, 7200], fn (): int => $this->query()->totalReviews());
    }

    public function getRecentReview(): ?Review
    {
        return $this->query()->recentReview();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRatingDistribution(): array
    {
        return $this->cached('dist', [3600, 7200], fn (): array => $this->query()->ratingDistribution());
    }

    protected function cachePrefix(): string
    {
        return 'review_summary';
    }

    private function query(): ReviewSummaryQuery
    {
        return resolve(ReviewSummaryQuery::class);
    }
}
