<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;

class HomeController extends Controller
{
    /**
     * Show the storefront home page.
     */
    public function __invoke()
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn ($q) => $q->where('is_active', true)->where('is_featured', true)])
            ->orderBy('sort_order')
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('home', compact('categories', 'storeName'));
    }
}
