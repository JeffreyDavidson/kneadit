<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuController extends Controller
{
    /**
     * Show the storefront menu page.
     */
    public function __invoke(): View
    {
        $categories = Category::query()->active()
            ->with(['products' => fn (HasMany $q) => $q->where('is_active', true)->orderBy('name'), 'products.seasonalItems'])
            ->orderBy('sort_order')
            ->get();

        $storeName = settings('store_name', 'Our Bakery');

        return view('menu', compact('categories', 'storeName'));
    }
}
