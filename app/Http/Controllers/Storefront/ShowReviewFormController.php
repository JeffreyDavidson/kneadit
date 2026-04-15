<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowReviewFormController extends Controller
{
    public function __invoke(Order $order, Request $request, TenantSettings $settings): View
    {
        $order->load(['customer', 'orderItems.product']);
        $content = settingsPageContent('submit_review');

        return view('storefront.submit-review', [
            'settings' => $settings,
            'order' => $order,
            'content' => $content,
            'ratingDescriptions' => $content['rating_descriptions'] ?? config('kneadit.default_rating_descriptions'),
            'prefilledRating' => $request->query('rating'),
        ]);
    }
}
