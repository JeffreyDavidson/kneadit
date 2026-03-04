<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Review;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReviewAnalytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Review Analytics';
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';
    protected string $view = 'filament.pages.review-analytics';

    public function getOverallStats(): array
    {
        $reviews = Review::all();
        
        if ($reviews->isEmpty()) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'approval_rate' => 0,
            ];
        }

        $totalReviews = $reviews->count();
        $averageRating = $reviews->avg('rating');
        $approvedReviews = $reviews->where('is_approved', true)->count();
        $approvalRate = $totalReviews > 0 ? ($approvedReviews / $totalReviews) * 100 : 0;

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 1),
            'approval_rate' => round($approvalRate, 1),
            'approved_reviews' => $approvedReviews,
        ];
    }

    public function getRatingDistribution(): array
    {
        $distribution = Review::select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = array_sum($distribution);
        $ratingStats = [];

        for ($i = 5; $i >= 1; $i--) {
            $count = $distribution[$i] ?? 0;
            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
            
            $ratingStats[] = [
                'rating' => $i,
                'count' => $count,
                'percentage' => round($percentage, 1),
            ];
        }

        return $ratingStats;
    }

    public function getMonthlyTrend(): array
    {
        $monthlyData = Review::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(rating) as avg_rating')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $trend = [];
        $startDate = now()->subMonths(11)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $month = $startDate->copy()->addMonths($i);
            $monthKey = $month->format('Y-m');
            $monthName = $month->format('M Y');
            
            $monthData = $monthlyData->firstWhere('month', $monthKey);
            
            $trend[] = [
                'month' => $monthName,
                'month_key' => $monthKey,
                'count' => $monthData ? $monthData->count : 0,
                'avg_rating' => $monthData ? round($monthData->avg_rating, 1) : 0,
            ];
        }

        return $trend;
    }

    public function getTopReviewedProducts(): Collection
    {
        return Product::withCount(['reviews' => function ($query) {
                $query->where('is_approved', true);
            }])
            ->with(['reviews' => function ($query) {
                $query->where('is_approved', true);
            }])
            ->having('reviews_count', '>', 0)
            ->orderByDesc('reviews_count')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $averageRating = $product->reviews->avg('rating');
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'reviews_count' => $product->reviews_count,
                    'average_rating' => $averageRating ? round($averageRating, 1) : 0,
                ];
            });
    }

    public function getRecentReviews(): Collection
    {
        return Review::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'customer_name' => $review->customer_name,
                    'product_name' => $review->product ? $review->product->name : 'Unknown Product',
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'is_approved' => $review->is_approved,
                    'is_featured' => $review->is_featured,
                    'created_at' => $review->created_at,
                ];
            });
    }

    public function getSentimentAnalysis(): array
    {
        $reviews = Review::whereNotNull('comment')
            ->where('comment', '!=', '')
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
            ];
        }

        $positive = 0;
        $neutral = 0;
        $negative = 0;

        foreach ($reviews as $review) {
            // Simple sentiment analysis based on rating and keywords
            $rating = $review->rating;
            $comment = strtolower($review->comment);
            
            $positiveWords = ['amazing', 'excellent', 'perfect', 'love', 'best', 'wonderful', 'fantastic', 'delicious', 'great'];
            $negativeWords = ['terrible', 'awful', 'bad', 'hate', 'worst', 'disgusting', 'horrible', 'disappointing'];
            
            $hasPositive = collect($positiveWords)->some(fn($word) => str_contains($comment, $word));
            $hasNegative = collect($negativeWords)->some(fn($word) => str_contains($comment, $word));
            
            if ($rating >= 4 && ($hasPositive || !$hasNegative)) {
                $positive++;
            } elseif ($rating <= 2 || $hasNegative) {
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