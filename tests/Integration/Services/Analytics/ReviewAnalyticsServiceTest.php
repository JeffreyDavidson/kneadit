<?php

use App\Models\Engagement\Review;
use App\Services\Analytics\ReviewAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('overall stats returns zero counts with no reviews', function () {
    $service = new ReviewAnalyticsService;
    $stats = $service->getOverallStats();

    expect($stats['total_reviews'])->toBe(0)
        ->and($stats['approved_reviews'])->toBe(0)
        ->and($stats['average_rating'])->toBe(0);
});

test('overall stats calculates correctly with reviews', function () {
    Review::factory()->approved()->create(['rating' => 5]);
    Review::factory()->approved()->create(['rating' => 3]);
    Review::factory()->create(['rating' => 4]);

    $service = new ReviewAnalyticsService;
    $stats = $service->getOverallStats();

    expect($stats['total_reviews'])->toBe(3)
        ->and($stats['approved_reviews'])->toBe(2)
        ->and($stats['average_rating'])->toBe(4.0);
});
