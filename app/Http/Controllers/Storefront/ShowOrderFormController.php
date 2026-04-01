<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ShowOrderFormController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        $categories = Category::query()
            ->active()
            ->withActiveProducts()
            ->orderBy('sort_order')
            ->get();

        return view('storefront.order', [
            'settings' => $settings,
            'categories' => $categories,
        ]);
    }
}
