<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Queries\StorefrontStatsQuery;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    /**
     * Show the storefront about page.
     */
    public function __invoke(TenantSettings $settings): View
    {
        $stats = StorefrontStatsQuery::get();

        return view('about', [
            'settings' => $settings,
            'content' => settingsPageContent('about'),
            'customerCount' => $stats['customer_count'],
            'avgRating' => $stats['avg_rating'],
            'orderCount' => $stats['order_count'],
        ]);
    }
}
