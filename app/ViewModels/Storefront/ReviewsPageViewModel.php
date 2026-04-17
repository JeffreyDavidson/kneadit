<?php

namespace App\ViewModels\Storefront;

use Illuminate\Pagination\LengthAwarePaginator;

class ReviewsPageViewModel
{
    public readonly float $avgRating;

    public readonly string $formattedAvgRating;

    public readonly int $totalReviews;

    public readonly int $fiveStarPct;

    /** @var array<int, array{count: int, pct: float}> keyed 5→1 */
    public readonly array $ratingBreakdown;

    /**
     * @param object{avg_rating: float, total_count: int} $stats
     * @param array<int, int> $starCounts keyed by star rating (5→1)
     */
    public function __construct(LengthAwarePaginator $reviews, object $stats, array $starCounts)
    {
        $this->avgRating = (float) $stats->avg_rating;
        $this->formattedAvgRating = number_format($this->avgRating, 1);
        $this->totalReviews = (int) $stats->total_count;

        $this->fiveStarPct = $this->totalReviews > 0
            ? (int) round(($starCounts[5] / $this->totalReviews) * 100)
            : 0;

        $breakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $starCounts[$star] ?? 0;
            $breakdown[$star] = [
                'count' => $count,
                'pct' => $this->totalReviews > 0 ? (float) (($count / $this->totalReviews) * 100) : 0.0,
            ];
        }
        $this->ratingBreakdown = $breakdown;
    }
}
