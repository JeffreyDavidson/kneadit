<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuController extends Controller
{
    /**
     * Show the storefront menu page.
     */
    public function __invoke(): View
    {
        $categories = Category::query()->where('is_active', true)
            ->with(['products' => fn (HasMany $q) => $q->where('is_active', true)->orderBy('name'), 'products.seasonalItems'])
            ->orderBy('sort_order')
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('menu', compact('categories', 'storeName'));
    }
}
