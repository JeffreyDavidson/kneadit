<?php

use App\Models\Engagement\Review;
use App\ViewModels\Storefront\ReviewsPageViewModel;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @param list<int> $ratings
 * @return LengthAwarePaginator<int, Review>
 */
function makeReviewCollection(array $ratings): LengthAwarePaginator
{
    $items = collect($ratings)->map(function (int $rating): Review {
        $review = new Review;
        $review->rating = $rating;

        return $review;
    });

    return new LengthAwarePaginator($items, count($ratings), 12);
}

/** @return object{avg_rating: float, total_count: int} */
function makeStats(float $avgRating, int $totalCount): object
{
    return (object) ['avg_rating' => $avgRating, 'total_count' => $totalCount];
}

/**
 * @param list<int> $ratings
 * @return array<int, int>
 */
function makeStarCounts(array $ratings): array
{
    $counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    foreach ($ratings as $rating) {
        $counts[$rating]++;
    }

    return $counts;
}

test('computes average rating from stats', function () {
    $ratings = [5, 4, 3];
    $vm = new ReviewsPageViewModel(
        makeReviewCollection($ratings),
        makeStats(4.0, 3),
        makeStarCounts($ratings),
    );

    expect($vm->avgRating)->toBe(4.0);
});

test('computes total reviews from stats', function () {
    $ratings = [5, 5, 3];
    $vm = new ReviewsPageViewModel(
        makeReviewCollection($ratings),
        makeStats(4.33, 3),
        makeStarCounts($ratings),
    );

    expect($vm->totalReviews)->toBe(3);
});

test('computes five star percentage from global counts', function () {
    $ratings = [5, 5, 4, 3];
    $vm = new ReviewsPageViewModel(
        makeReviewCollection($ratings),
        makeStats(4.25, 4),
        makeStarCounts($ratings),
    );

    expect($vm->fiveStarPct)->toBe(50);
});

test('computes rating breakdown keyed 5 to 1', function () {
    $ratings = [5, 5, 4, 3, 1];
    $vm = new ReviewsPageViewModel(
        makeReviewCollection($ratings),
        makeStats(3.6, 5),
        makeStarCounts($ratings),
    );

    expect($vm->ratingBreakdown)
        ->toHaveKeys([5, 4, 3, 2, 1])
        ->and($vm->ratingBreakdown[5]['count'])->toBe(2)
        ->and($vm->ratingBreakdown[4]['count'])->toBe(1)
        ->and($vm->ratingBreakdown[3]['count'])->toBe(1)
        ->and($vm->ratingBreakdown[2]['count'])->toBe(0)
        ->and($vm->ratingBreakdown[1]['count'])->toBe(1)
        ->and($vm->ratingBreakdown[5]['pct'])->toBe(40.0)
        ->and($vm->ratingBreakdown[2]['pct'])->toBe(0.0);
});

test('handles zero reviews gracefully', function () {
    $vm = new ReviewsPageViewModel(
        makeReviewCollection([]),
        makeStats(0, 0),
        makeStarCounts([]),
    );

    expect($vm->avgRating)->toBe(0.0)
        ->and($vm->totalReviews)->toBe(0)
        ->and($vm->fiveStarPct)->toBe(0)
        ->and($vm->ratingBreakdown[5]['pct'])->toBe(0.0)
        ->and($vm->ratingBreakdown[1]['pct'])->toBe(0.0);
});

test('uses global star counts not paginated subset for percentages', function () {
    $allRatings = [5, 5, 5, 5, 5, 4, 4, 4, 3, 3, 2, 1];
    $paginatedRatings = [5, 4, 3];

    $vm = new ReviewsPageViewModel(
        makeReviewCollection($paginatedRatings),
        makeStats(3.5, 12),
        makeStarCounts($allRatings),
    );

    expect($vm->fiveStarPct)->toBe(42)
        ->and($vm->ratingBreakdown[5]['count'])->toBe(5)
        ->and($vm->ratingBreakdown[4]['count'])->toBe(3)
        ->and($vm->ratingBreakdown[3]['count'])->toBe(2)
        ->and($vm->ratingBreakdown[2]['count'])->toBe(1)
        ->and($vm->ratingBreakdown[1]['count'])->toBe(1);
});
