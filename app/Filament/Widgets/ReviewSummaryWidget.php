<?php

namespace App\Filament\Widgets;

use App\Models\Engagement\Review;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class ReviewSummaryWidget extends Widget
{
    protected static ?int $sort = 19;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.review-summary-widget';

    public function getAverageRating(): float
    {
        return Cache::flexible('review_summary_avg_' . (tenant()?->getTenantKey() ?? 'none'), [3600, 7200], function (): float {
            return round((float) Review::query()->approved()->avg('rating'), 1);
        });
    }

    public function getTotalReviews(): int
    {
        return Cache::flexible('review_summary_total_' . (tenant()?->getTenantKey() ?? 'none'), [3600, 7200], function (): int {
            return Review::query()->approved()->count();
        });
    }

    public function getRecentReview(): ?Review
    {
        return Review::query()->approved()->latest()->first();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRatingDistribution(): array
    {
        return Cache::flexible('review_summary_dist_' . (tenant()?->getTenantKey() ?? 'none'), [3600, 7200], function (): array {
            $counts = Review::query()->approved()
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

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
}
