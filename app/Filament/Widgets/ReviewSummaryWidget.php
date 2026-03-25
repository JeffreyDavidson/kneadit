<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Widgets\Widget;

class ReviewSummaryWidget extends Widget
{
    protected static ?int $sort = 19;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.review-summary-widget';

    public function getAverageRating(): float
    {
        return round((float) Review::query()->where('is_approved', true)->avg('rating'), 1);
    }

    public function getTotalReviews(): int
    {
        return Review::query()->where('is_approved', true)->count();
    }

    public function getRecentReview(): ?Review
    {
        return Review::query()->where('is_approved', true)->latest()
            ->first();
    }

    /** @return array<int, array<string, mixed>> */
    public function getRatingDistribution(): array
    {
        $counts = Review::query()->where('is_approved', true)
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $total = array_sum($counts);
        $distribution = [];

        for ($i = 5; $i >= 1; $i--) {
            $count = $counts[$i] ?? 0;
            $distribution[$i] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return $distribution;
    }
}
