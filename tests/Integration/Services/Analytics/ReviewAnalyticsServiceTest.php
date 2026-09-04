<?php

use App\Models\Engagement\Review;
use App\Services\Analytics\ReviewAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

afterEach(fn () => Date::setTestNow());

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

test('monthly trend aggregates the latest twelve calendar months', function () {
    Date::setTestNow('2026-09-04 12:00:00');

    Review::factory()->create(['rating' => 2, 'created_at' => Date::parse('2025-10-15')]);
    Review::factory()->create(['rating' => 3, 'created_at' => Date::parse('2026-08-01')]);
    Review::factory()->create(['rating' => 4, 'created_at' => Date::parse('2026-08-20')]);
    Review::factory()->create(['rating' => 5, 'created_at' => Date::parse('2026-09-01')]);
    Review::factory()->create(['rating' => 1, 'created_at' => Date::parse('2025-09-30')]);

    $trend = (new ReviewAnalyticsService)->getMonthlyTrend();
    $months = collect($trend)->keyBy('month_key');

    expect($trend)->toHaveCount(12)
        ->and($trend[0]['month'])->toBe('Oct 2025')
        ->and($months['2025-10']['count'])->toBe(1)
        ->and($months['2026-08']['count'])->toBe(2)
        ->and($months['2026-08']['avg_rating'])->toBe(3.5)
        ->and($months['2026-09']['count'])->toBe(1)
        ->and($months)->not->toHaveKey('2025-09');
});
