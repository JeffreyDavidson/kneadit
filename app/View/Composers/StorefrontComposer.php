<?php

namespace App\View\Composers;

use Illuminate\View\View;

class StorefrontComposer
{
    public function compose(View $view): void
    {
        $storeName = settings('store_name', 'Artisan Bakery');
        $storeLogo = settings('store_logo');

        $view->with([
            'ogStoreName' => $storeName,
            'ogDescription' => settings('store_tagline', "{$storeName} — Fresh baked goods made with love"),
            'ogLogo' => $storeLogo ? asset("storage/{$storeLogo}") : null,
            'storefrontTheme' => settings('storefront_theme', 'classic'),
        ]);
    }
}
