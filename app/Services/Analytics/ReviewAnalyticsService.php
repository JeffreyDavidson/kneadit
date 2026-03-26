<?php

namespace App\Services\Analytics;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReviewAnalyticsService
{
    /** @return array<string, mixed> */
    public function getOverallStats(): array
    {
        $totalReviews = Review::query()->count();

        if ($totalReviews === 0) {
            return ['total_reviews' => 0, 'average_rating' => 0, 'approval_rate' => 0];
        }

        $averageRating = (float) Review::query()->avg('rating');
        $approvedReviews = Review::query()->approved()->count();
        $approvalRate = ($approvedReviews / $totalReviews) * 100;

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 1),
            'approval_rate' => round($approvalRate, 1),
            'approved_reviews' => $approvedReviews,
        ];
    }

    /** @return array<int, array<string, mixed>> */
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
            $percentage = $totalReviews > 0 ? ((int) $count / $totalReviews) * 100 : 0;

            $ratingStats[] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => round($percentage, 1),
            ];
        }

        return $ratingStats;
    }

    /** @return array<int, array<string, mixed>> */
    public function getMonthlyTrend(): array
    {
        $monthlyData = Review::query()
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'), DB::raw('AVG(rating) as avg_rating'))
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $trend = [];
        $startDate = now()->subMonths(11)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $month = $startDate->copy()->addMonths($i);
            $monthKey = $month->format('Y-m');
            $monthData = $monthlyData->firstWhere('month', $monthKey);

            $trend[] = [
                'month' => $month->format('M Y'),
                'month_key' => $monthKey,
                'count' => $monthData ? $monthData->count : 0,
                'avg_rating' => $monthData ? round($monthData->avg_rating ?? 0, 1) : 0,
            ];
        }

        return $trend;
    }

    /** @return Collection<int, Product> */
    public function getTopReviewedProducts(): Collection
    {
        return Product::query()->withCount(['reviews' => function (Builder $query) {
            $query->where('is_approved', true);
        }])
            ->with(['reviews' => function (Builder $query) {
                $query->where('is_approved', true);
            }])
            ->having('reviews_count', '>', 0)
            ->orderByDesc('reviews_count')
            ->limit(10)
            ->get()
            ->map(function (Product $product) {
                $averageRating = $product->reviews->avg('rating');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'reviews_count' => $product->reviews_count,
                    'average_rating' => $averageRating ? round($averageRating, 1) : 0,
                ];
            });
    }

    /** @return Collection<int, Review> */
    public function getRecentReviews(): Collection
    {
        return Review::with('product')->latest()
            ->limit(10)
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->id,
                'customer_name' => $review->customer_name,
                'product_name' => $review->product ? $review->product->name : 'Unknown Product',
                'rating' => $review->rating,
                'comment' => $review->comment,
                'is_approved' => $review->is_approved,
                'is_featured' => $review->is_featured,
                'created_at' => $review->created_at,
            ]);
    }

    /** @return array<string, float> */
    public function getSentimentAnalysis(): array
    {
        $reviews = Review::query()->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->get();

        if ($reviews->isEmpty()) {
            return ['positive' => 0, 'neutral' => 0, 'negative' => 0];
        }

        $positive = 0;
        $neutral = 0;
        $negative = 0;

        $positiveWords = ['amazing', 'excellent', 'perfect', 'love', 'best', 'wonderful', 'fantastic', 'delicious', 'great'];
        $negativeWords = ['terrible', 'awful', 'bad', 'hate', 'worst', 'disgusting', 'horrible', 'disappointing'];

        foreach ($reviews as $review) {
            $comment = strtolower((string) $review->comment);
            $hasPositive = collect($positiveWords)->contains(fn (string $word) => str_contains($comment, $word));
            $hasNegative = collect($negativeWords)->contains(fn (string $word) => str_contains($comment, $word));

            if ($review->rating >= 4 && ($hasPositive || ! $hasNegative)) {
                $positive++;
            } elseif ($review->rating <= 2 || $hasNegative) {
                $negative++;
            } else {
                $neutral++;
            }
        }

        $total = $positive + $neutral + $negative;

        return [
            'positive' => $total > 0 ? round(($positive / $total) * 100, 1) : 0,
            'neutral' => $total > 0 ? round(($neutral / $total) * 100, 1) : 0,
            'negative' => $total > 0 ? round(($negative / $total) * 100, 1) : 0,
        ];
    }
}
