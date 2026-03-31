<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class HomeController extends Controller
{
    /**
     * Show the storefront home page.
     */
    public function __invoke(TenantSettings $settings): View
    {
        $categories = Category::query()->active()
            ->with([
                'products' => fn (Builder $q) => $q->where('is_active', true)->where('is_featured', true),
            ])
            ->orderBy('sort_order')
            ->get();

        $sections = collect($settings->homepageSections)->filter(fn (array $s) => $s['visible'] ?? true)->sortBy('order');

        return view('home', [
            'settings' => $settings,
            'categories' => $categories,
            'homepageSections' => $settings->homepageSections,
            'sections' => $sections,
        ]);
    }
}
