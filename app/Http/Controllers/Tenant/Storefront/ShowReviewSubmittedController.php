<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ShowReviewSubmittedController extends Controller
{
    public function __invoke(Order $order, TenantSettings $settings): View
    {
        $content = settingsPageContent('submit_review');

        return view('tenant.storefront.submit-review', [
            'settings' => $settings,
            'order' => $order,
            'content' => $content,
            'ratingDescriptions' => $content['rating_descriptions'] ?? config('kneadit.default_rating_descriptions'),
            'prefilledRating' => null,
            'success' => true,
        ]);
    }
}
