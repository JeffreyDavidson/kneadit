<?php

use App\DataTransferObjects\Analytics\ConversionFunnelStep;
use App\DataTransferObjects\Analytics\DailyPageViewCount;
use App\DataTransferObjects\Analytics\DateSeries;
use App\DataTransferObjects\Analytics\PageViewCount;
use App\DataTransferObjects\Analytics\RatingDistributionEntry;
use App\DataTransferObjects\Analytics\RecentReview;
use App\DataTransferObjects\Analytics\ReviewAnalyticsSummary;
use App\DataTransferObjects\Analytics\ReviewMonthlyTrend;
use App\DataTransferObjects\Analytics\SentimentAnalysis;
use App\DataTransferObjects\Analytics\TopReviewedProduct;
use App\DataTransferObjects\Analytics\TopViewedProduct;
use Carbon\Carbon;

test('date series fills missing integer values', function () {
    $series = DateSeries::between(Carbon::parse('2026-09-01'), Carbon::parse('2026-09-03'));

    expect($series->dates())->toBe(['2026-09-01', '2026-09-02', '2026-09-03'])
        ->and($series->fillIntegers(['2026-09-02' => 4]))->toBe([0, 4, 0]);
});

test('analytics DTOs expose stable array boundaries', function () {
    $createdAt = Carbon::parse('2026-09-13 12:00:00');

    expect((new PageViewCount('home', 3))->page)->toBe('home')
        ->and((new DailyPageViewCount('2026-09-13', 3))->views)->toBe(3)
        ->and((new TopViewedProduct('Cake', 3))->name)->toBe('Cake')
        ->and(new ConversionFunnelStep('Home', 3, 100, null)->toArray())
        ->toMatchArray(['label' => 'Home', 'count' => 3])
        ->and(new ReviewAnalyticsSummary(3, 4.5, 66.7, 2)->toArray())
        ->toMatchArray(['total_reviews' => 3, 'approved_reviews' => 2])
        ->and(new RatingDistributionEntry(5, 2, 66.7)->toArray())
        ->toMatchArray(['rating' => 5, 'count' => 2])
        ->and(new ReviewMonthlyTrend('Sep 2026', '2026-09', 2, 4.5)->toArray())
        ->toMatchArray(['month_key' => '2026-09', 'avg_rating' => 4.5])
        ->and(new TopReviewedProduct(1, 'Cake', 2, 4.5)->toArray())
        ->toMatchArray(['id' => 1, 'reviews_count' => 2])
        ->and(new RecentReview(1, 'Baker', 'Cake', 5, 'Great', true, false, $createdAt)->toArray())
        ->toMatchArray(['customer_name' => 'Baker', 'created_at' => $createdAt])
        ->and(new SentimentAnalysis(80, 10, 10)->toArray())
        ->toBe(['positive' => 80.0, 'neutral' => 10.0, 'negative' => 10.0]);
});
