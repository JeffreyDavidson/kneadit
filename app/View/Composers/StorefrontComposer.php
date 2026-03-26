<?php

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\View\View;

class StorefrontComposer
{
    public function compose(View $view): void
    {
        $storeName = Setting::get('store_name', 'Artisan Bakery');
        $storeLogo = Setting::get('store_logo');

        $view->with([
            'ogStoreName' => $storeName,
            'ogDescription' => Setting::get('store_tagline', "{$storeName} — Fresh baked goods made with love"),
            'ogLogo' => $storeLogo ? asset("storage/{$storeLogo}") : null,
            'storefrontTheme' => Setting::get('storefront_theme', 'classic'),
        ]);
    }
}
