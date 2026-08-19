<?php

use App\Models\Engagement\Review;
use App\Queries\Engagement\ReviewSummaryQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('summarizes approved reviews only', function () {
    Review::factory()->approved()->create(['rating' => 5, 'created_at' => now()->subDay()]);
    $recent = Review::factory()->approved()->create(['rating' => 3, 'created_at' => now()]);
    Review::factory()->pending()->create(['rating' => 1]);

    $query = resolve(ReviewSummaryQuery::class);
    $distribution = $query->ratingDistribution();

    expect($query->averageRating())->toBe(4.0)
        ->and($query->totalReviews())->toBe(2)
        ->and($query->recentReview()?->is($recent))->toBeTrue()
        ->and($distribution[5])->toBe(['count' => 1, 'percentage' => 50.0])
        ->and($distribution[1])->toBe(['count' => 0, 'percentage' => 0]);
});
