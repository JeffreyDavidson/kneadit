<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\CreateReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Order $order, StoreReviewRequest $request, CreateReview $createReview, TenantSettings $settings): View
    {
        $validated = $request->validated();

        $createReview(
            order: $order,
            rating: (int) $validated['rating'],
            comment: $validated['comment'] ?? null,
            photo: $request->file('photo'),
        );

        $content = settingsPageContent('submit_review');
        $ratingDescriptions = $content['rating_descriptions'] ?? ['', 'Could be better', 'It was okay', 'Pretty good!', 'Really great!', 'Absolutely amazing!'];

        return view('submit-review', [
            'settings' => $settings,
            'order' => $order,
            'prefilledRating' => null,
            'content' => $content,
            'ratingDescriptions' => $ratingDescriptions,
            'success' => true,
        ]);
    }

    public function show(Order $order, Request $request, TenantSettings $settings): View
    {
        $order->load(['customer', 'orderItems.product']);
        $prefilledRating = $request->query('rating');

        $content = settingsPageContent('submit_review');
        $ratingDescriptions = $content['rating_descriptions'] ?? ['', 'Could be better', 'It was okay', 'Pretty good!', 'Really great!', 'Absolutely amazing!'];

        return view('submit-review', [
            'settings' => $settings,
            'order' => $order,
            'prefilledRating' => $prefilledRating,
            'content' => $content,
            'ratingDescriptions' => $ratingDescriptions,
        ]);
    }
}
