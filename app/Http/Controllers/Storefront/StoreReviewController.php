<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\CreateReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreReviewRequest;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class StoreReviewController extends Controller
{
    public function __invoke(Order $order, StoreReviewRequest $request, CreateReview $createReview, TenantSettings $settings): View
    {
        $createReview(
            order: $order,
            rating: (int) $request->validated('rating'),
            comment: $request->validated('comment'),
            photo: $request->file('photo'),
        );

        $content = settingsPageContent('submit_review');

        return view('storefront.submit-review', [
            'settings' => $settings,
            'order' => $order,
            'content' => $content,
            'ratingDescriptions' => $content['rating_descriptions'] ?? config('kneadit.default_rating_descriptions'),
            'prefilledRating' => null,
            'success' => true,
        ]);
    }
}
