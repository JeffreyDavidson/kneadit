<?php

namespace App\Queries\Engagement;

use App\Models\Engagement\Review;
use Illuminate\Support\Arr;

final class ReviewSummaryQuery
{
    public function averageRating(): float
    {
        return round((float) Review::query()->approved()->avg('rating'), 1);
    }

    public function totalReviews(): int
    {
        return Review::query()->approved()->count();
    }

    public function recentReview(): ?Review
    {
        return Review::query()->approved()->latest()->first();
    }

    /** @return array<int, array{count: int, percentage: int|float}> */
    public function ratingDistribution(): array
    {
        $counts = Review::query()->approved()
            ->selectRaw('rating, count(*) as aggregate')
            ->groupBy('rating')
            ->get()
            ->mapWithKeys(function (Review $review): array {
                $attributes = $review->getAttributes();

                return [
                    Arr::integer($attributes, 'rating') => Arr::integer($attributes, 'aggregate'),
                ];
            });
        $total = $counts->reduce(fn (int $total, int $count): int => $total + $count, 0);
        $distribution = [];

        for ($rating = 5; $rating >= 1; $rating--) {
            $count = $counts->get($rating, 0);
            $distribution[$rating] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return $distribution;
    }
}
