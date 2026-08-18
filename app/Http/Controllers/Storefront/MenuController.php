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
        $configuredEyebrow = $content['hero_eyebrow'] ?? null;
        $configuredDescription = $content['cta_description'] ?? null;
        $heroEyebrow = str_replace('{{store_name}}', $settings->store->name, is_string($configuredEyebrow) ? $configuredEyebrow : $settings->store->name);
        $ctaDesc = str_replace('{{lead_time}}', $leadTimeHours, is_string($configuredDescription) ? $configuredDescription : 'All orders need ' . $leadTimeHours . ' hours notice. Place yours now.');

        return view('storefront.menu', [
            'settings' => $settings,
            'categories' => $categories,
            'content' => $content,
            'heroEyebrow' => $heroEyebrow,
            'ctaDesc' => $ctaDesc,
            'storefrontTheme' => $settings->branding->storefrontTheme,
        ]);
    }
}
