<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\CachesWidgetData;
use App\Filament\Widgets\Concerns\HasDashboardSize;
use App\Models\Engagement\Review;
use Filament\Widgets\Widget;

class ReviewSummaryWidget extends Widget
{
    use CachesWidgetData;
    use HasDashboardSize;

    protected static ?int $sort = 19;

    protected string $view = 'filament.widgets.review-summary-widget';

    public function getAverageRating(): float
    {
        return $this->cached('avg', [3600, 7200], fn (): float => round((float) Review::query()->approved()->avg('rating'), 1));
    }

    public function getTotalReviews(): int
    {
        return $this->cached('total', [3600, 7200], fn (): int => Review::query()->approved()->count());
    }

    public function getRecentReview(): ?Review
    {
        return Review::query()->approved()->latest()->first();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRatingDistribution(): array
    {
        return $this->cached('dist', [3600, 7200], function (): array {
            $counts = Review::query()->approved()
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $counts = array_map(static fn (mixed $count): int => (int) $count, $counts);
            $total = array_sum($counts);
            $distribution = [];

            for ($i = 5; $i >= 1; $i--) {
                $count = $counts[$i] ?? 0;
                $distribution[$i] = [
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100) : 0,
                ];
            }

            return $distribution;
        });
    }

    protected function cachePrefix(): string
    {
        return 'review_summary';
    }
}
