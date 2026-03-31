<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuController extends Controller
{
    /**
     * Show the storefront menu page.
     */
    public function __invoke(TenantSettings $settings): View
    {
        $categories = Category::query()->active()
            ->with(['products' => fn (HasMany $q) => $q->where('is_active', true)->orderBy('name'), 'products.seasonalItems'])
            ->orderBy('sort_order')
            ->get();

        $content = settingsPageContent('menu');
        $heroEyebrow = str_replace('{{store_name}}', $settings->storeName, $content['hero_eyebrow'] ?? $settings->storeName);
        $ctaDesc = str_replace('{{lead_time}}', (string) $settings->leadTimeHours, $content['cta_description'] ?? 'All orders need ' . $settings->leadTimeHours . ' hours notice. Place yours now.');

        return view('menu', [
            'settings' => $settings,
            'categories' => $categories,
            'content' => $content,
            'heroEyebrow' => $heroEyebrow,
            'ctaDesc' => $ctaDesc,
        ]);
    }
}
