<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class ReviewsController extends Controller
{
    /**
     * Show the storefront reviews listing page.
     */
    public function __invoke(): View
    {
        $reviews = Review::query()->forDisplay()->paginate(12);
        $stats = Review::query()->statistics();

        $storeName = settings('store_name', 'Our Bakery');
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('reviews');

        $avgRating = (float) $stats->avg_rating;
        $totalReviews = (int) $stats->total_count;
        $fiveStarCount = $reviews->where('rating', 5)->count();
        $fiveStarPct = $totalReviews > 0 ? round(($fiveStarCount / $totalReviews) * 100) : 0;
        $featured = $reviews->first();

        return view('reviews', compact(
            'reviews', 'avgRating', 'totalReviews',
            'storeName', 'heroImageUrl', 'content',
            'fiveStarCount', 'fiveStarPct', 'featured',
        ));
    }
}
