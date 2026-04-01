<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\CreateReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreReviewRequest;
use App\Models\Order;
use App\Models\Review;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(TenantSettings $settings): View
    {
        $reviews = Review::query()->forDisplay()->paginate(12);
        $stats = Review::query()->statistics();

        $content = settingsPageContent('reviews');

        $avgRating = (float) $stats->avg_rating;
        $totalReviews = (int) $stats->total_count;
        $fiveStarCount = $reviews->where('rating', 5)->count();
        $fiveStarPct = $totalReviews > 0 ? round(($fiveStarCount / $totalReviews) * 100) : 0;

        return view('reviews', [
            'settings' => $settings,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'totalReviews' => $totalReviews,
            'content' => $content,
            'fiveStarCount' => $fiveStarCount,
            'fiveStarPct' => $fiveStarPct,
            'featured' => $reviews->first(),
        ]);
    }

    public function store(Order $order, StoreReviewRequest $request, CreateReview $createReview, TenantSettings $settings): View
    {
        $createReview(
            order: $order,
            rating: (int) $request->validated('rating'),
            comment: $request->validated('comment'),
            photo: $request->file('photo'),
        );

        return view('submit-review', [
            ...$this->sharedViewData($settings, $order),
            'prefilledRating' => null,
            'success' => true,
        ]);
    }

    public function show(Order $order, Request $request, TenantSettings $settings): View
    {
        $order->load(['customer', 'orderItems.product']);

        return view('submit-review', [
            ...$this->sharedViewData($settings, $order),
            'prefilledRating' => $request->query('rating'),
        ]);
    }

    /** @return array<string, mixed> */
    private function sharedViewData(TenantSettings $settings, Order $order): array
    {
        $content = settingsPageContent('submit_review');

        return [
            'settings' => $settings,
            'order' => $order,
            'content' => $content,
            'ratingDescriptions' => $content['rating_descriptions'] ?? config('kneadit.default_rating_descriptions'),
        ];
    }
}
