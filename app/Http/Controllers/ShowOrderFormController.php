<?php

namespace App\Http\Controllers;

use App\Models\Category;
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

        return view('order', [
            'settings' => $settings,
            'categories' => $categories,
        ]);
    }
}
