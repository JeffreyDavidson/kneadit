<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewsController extends Controller
{
    /**
     * Show the storefront reviews listing page.
     */
    public function __invoke()
    {
        $reviews = Review::where('is_approved', true)
            ->with('product')
            ->latest()
            ->paginate(12);

        $avgRating = Review::where('is_approved', true)->avg('rating');
        $totalReviews = Review::where('is_approved', true)->count();

        return view('reviews', compact('reviews', 'avgRating', 'totalReviews'));
    }
}
