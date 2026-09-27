<?php

use App\Models\Engagement\Review;
use App\Services\Analytics\ReviewAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

afterEach(fn () => Date::setTestNow());

test('overall stats returns zero counts with no reviews', function () {
    $service = new ReviewAnalyticsService;
    $stats = $service->getOverallStats();

    expect($stats->totalReviews)->toBe(0)
        ->and($stats->approvedReviews)->toBe(0)
        ->and($stats->averageRating)->toBe(0.0);
});

test('overall stats calculates correctly with reviews', function () {
    Review::factory()->approved()->create(['rating' => 5]);
    Review::factory()->approved()->create(['rating' => 3]);
    Review::factory()->create(['rating' => 1]);

    $service = new ReviewAnalyticsService;
    $stats = $service->getOverallStats();

    expect($stats->totalReviews)->toBe(3)
        ->and($stats->approvedReviews)->toBe(2)
        ->and($stats->averageRating)->toBe(3.0)
        ->and($stats->approvalRate)->toBe(66.7);
});

test('overall stats uses one aggregate query', function () {
    Review::factory()->approved()->create(['rating' => 5]);
    Review::factory()->create(['rating' => 3]);

    DB::connection()->flushQueryLog();
    DB::connection()->enableQueryLog();

    $reviewQueries = collect();

    try {
        new ReviewAnalyticsService()->getOverallStats();

        $reviewQueries = collect(DB::connection()->getQueryLog())
            ->filter(fn (array $query): bool => str_contains($query['query'], 'reviews'));
    } finally {
        DB::connection()->disableQueryLog();
    }

    expect($reviewQueries)->toHaveCount(1);
});

test('monthly trend aggregates the latest twelve calendar months', function () {
    Date::setTestNow('2026-09-04 12:00:00');

    Review::factory()->create(['rating' => 2, 'created_at' => Date::parse('2025-10-15')]);
    Review::factory()->create(['rating' => 3, 'created_at' => Date::parse('2026-08-01')]);
    Review::factory()->create(['rating' => 4, 'created_at' => Date::parse('2026-08-20')]);
    Review::factory()->create(['rating' => 5, 'created_at' => Date::parse('2026-09-01')]);
    Review::factory()->create(['rating' => 1, 'created_at' => Date::parse('2025-09-30')]);

    $trend = (new ReviewAnalyticsService)->getMonthlyTrend();
    $months = collect($trend)->keyBy('monthKey');

    expect($trend)->toHaveCount(12)
        ->and($trend[0]->month)->toBe('Oct 2025')
        ->and($months['2025-10']->count)->toBe(1)
        ->and($months['2026-08']->count)->toBe(2)
        ->and($months['2026-08']->averageRating)->toBe(3.5)
        ->and($months['2026-09']->count)->toBe(1)
        ->and($months)->not->toHaveKey('2025-09');
});
