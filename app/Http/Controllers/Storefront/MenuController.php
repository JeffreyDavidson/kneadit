<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
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
            ->with([
                'products' => function (HasMany $q): void {
                    $q->where('is_active', true)->chaperone('category')->orderBy('name');
                },
                'products.seasonalItems',
            ])
            ->orderBy('sort_order')
            ->get();

        $content = settingsPageContent('menu');
        $leadTimeHours = (string) $settings->orders->leadTimeHours;
        $heroEyebrow = str_replace('{{store_name}}', $settings->store->name, $content['hero_eyebrow'] ?? $settings->store->name);
        $ctaDesc = str_replace('{{lead_time}}', $leadTimeHours, $content['cta_description'] ?? 'All orders need ' . $leadTimeHours . ' hours notice. Place yours now.');

        return view('storefront.menu', [
            'settings' => $settings,
            'categories' => $categories,
            'content' => $content,
            'heroEyebrow' => $heroEyebrow,
            'ctaDesc' => $ctaDesc,
            'storefrontTheme' => (string) settings('storefront_theme', 'classic'),
        ]);
    }
}
