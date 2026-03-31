<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ReviewsController extends Controller
{
    /**
     * Show the storefront reviews listing page.
     */
    public function __invoke(TenantSettings $settings): View
    {
        $reviews = Review::query()->forDisplay()->paginate(12);
        $stats = Review::query()->statistics();

        $content = settingsPageContent('reviews');

        $avgRating = (float) $stats->avg_rating;
        $totalReviews = (int) $stats->total_count;
        $fiveStarCount = $reviews->where('rating', 5)->count();
        $fiveStarPct = $totalReviews > 0 ? round(($fiveStarCount / $totalReviews) * 100) : 0;
        $featured = $reviews->first();

        return view('reviews', [
            'settings' => $settings,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'totalReviews' => $totalReviews,
            'content' => $content,
            'fiveStarCount' => $fiveStarCount,
            'fiveStarPct' => $fiveStarPct,
            'featured' => $featured,
        ]);
    }
}
