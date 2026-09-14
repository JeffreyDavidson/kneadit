<?php

namespace App\Services\Analytics;

use App\DataTransferObjects\Analytics\RatingDistributionEntry;
use App\DataTransferObjects\Analytics\RecentReview;
use App\DataTransferObjects\Analytics\ReviewAnalyticsSummary;
use App\DataTransferObjects\Analytics\ReviewMonthlyTrend;
use App\DataTransferObjects\Analytics\SentimentAnalysis;
use App\DataTransferObjects\Analytics\TopReviewedProduct;
use App\Models\Engagement\Review;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewAnalyticsService
{
    public function getOverallStats(): ReviewAnalyticsSummary
    {
        $totalReviews = Review::query()->count();

        if ($totalReviews === 0) {
            return new ReviewAnalyticsSummary(0, 0, 0, 0);
        }

        $averageRating = (float) Review::query()->avg('rating');
        $approvedReviews = Review::query()->approved()->count();
        $approvalRate = ($approvedReviews / $totalReviews) * 100;

        return new ReviewAnalyticsSummary(
            totalReviews: $totalReviews,
            averageRating: round($averageRating, 1),
            approvalRate: round($approvalRate, 1),
            approvedReviews: $approvedReviews,
        );
    }

    /** @return list<RatingDistributionEntry> */
    public function getRatingDistribution(): array
    {
        $distribution = Review::query()->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = array_sum($distribution);
        $ratingStats = [];

        for ($i = 5; $i >= 1; $i--) {
            $count = $distribution[$i] ?? 0;
            $count = is_int($count) ? $count : 0;
            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;

            $ratingStats[] = new RatingDistributionEntry(
                rating: $i,
                count: $count,
                percentage: round($percentage, 1),
            );
        }

        return $ratingStats;
    }

    /** @return list<ReviewMonthlyTrend> */
    public function getMonthlyTrend(): array
    {
        $startDate = Date::now()->subMonths(11)->startOfMonth();

        /** @var Collection<string, object{month: string, count: int, avg_rating: float}> $monthlyData */
        $monthlyData = Review::query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw("strftime('%Y-%m', created_at) AS month, COUNT(*) AS count, AVG(rating) AS avg_rating")
            ->groupByRaw("strftime('%Y-%m', created_at)")
            ->orderBy('month')
            ->toBase()
            ->get()
            ->keyBy('month');

        $trend = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $startDate->copy()->addMonths($i);
            $monthKey = $month->format('Y-m');
            $monthData = $monthlyData->get($monthKey);

            $trend[] = new ReviewMonthlyTrend(
                month: $month->format('M Y'),
                monthKey: $monthKey,
                count: $monthData ? (int) $monthData->count : 0,
                averageRating: $monthData ? round($monthData->avg_rating ?? 0, 1) : 0,
            );
        }

        return $trend;
    }

    /** @return Collection<int, TopReviewedProduct> */
    public function getTopReviewedProducts(): Collection
    {
        return Product::query()
            ->withCount(['reviews' => fn (Builder $q) => $q->where('is_approved', true)])
            ->withAvg(['reviews' => fn (Builder $q) => $q->where('is_approved', true)], 'rating')
            ->whereHas('reviews', fn (Builder $q) => $q->where('is_approved', true))
            ->orderByDesc('reviews_count')
            ->limit(10)
            ->get()
            ->map(fn (Product $product): TopReviewedProduct => new TopReviewedProduct(
                id: $product->id,
                name: $product->name,
                reviewsCount: $product->reviews_count,
                averageRating: $product->reviews_avg_rating ? round((float) $product->reviews_avg_rating, 1) : 0,
            ));
    }

    /** @return Collection<int, RecentReview> */
    public function getRecentReviews(): Collection
    {
        return Review::with('product')->latest()
            ->limit(10)
            ->get()
            ->map(fn (Review $review): RecentReview => new RecentReview(
                id: $review->id,
                customerName: $review->customer_name,
                productName: $review->product ? $review->product->name : 'Unknown Product',
                rating: $review->rating,
                comment: $review->comment,
                isApproved: $review->is_approved,
                isFeatured: $review->is_featured,
                createdAt: $review->created_at,
            ));
    }

    public function getSentimentAnalysis(): SentimentAnalysis
    {
        $reviews = Review::query()->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->select(['id', 'comment', 'rating'])
            ->cursor();

        $positive = 0;
        $neutral = 0;
        $negative = 0;

        $positiveWords = ['amazing', 'excellent', 'perfect', 'love', 'best', 'wonderful', 'fantastic', 'delicious', 'great'];
        $negativeWords = ['terrible', 'awful', 'bad', 'hate', 'worst', 'disgusting', 'horrible', 'disappointing'];

        foreach ($reviews as $review) {
            $comment = Str::lower((string) $review->comment);
            $hasPositive = collect($positiveWords)->contains(fn (string $word) => Str::contains($comment, $word));
            $hasNegative = collect($negativeWords)->contains(fn (string $word) => Str::contains($comment, $word));

            if ($review->rating >= 4 && ($hasPositive || ! $hasNegative)) {
                $positive++;
            } elseif ($review->rating <= 2 || $hasNegative) {
                $negative++;
            } else {
                $neutral++;
            }
        }

        $total = $positive + $neutral + $negative;

        return new SentimentAnalysis(
            positive: $total > 0 ? round(($positive / $total) * 100, 1) : 0,
            neutral: $total > 0 ? round(($neutral / $total) * 100, 1) : 0,
            negative: $total > 0 ? round(($negative / $total) * 100, 1) : 0,
        );
    }
}
