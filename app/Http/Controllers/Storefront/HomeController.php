<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the storefront home page.
     */
    public function __invoke(TenantSettings $settings): View
    {
        $categories = Category::query()
            ->active()
            ->withFeaturedProducts()
            ->orderBy('sort_order')
            ->get();

        return view('storefront.home', [
            'settings' => $settings,
            'categories' => $categories,
            'sections' => $settings->visibleHomepageSections(),
        ]);
    }
}
