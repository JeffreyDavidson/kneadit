<?php

namespace App\View\Components\Layouts;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Storefront extends Component
{
    public function __construct(
        public string $title = '',
        public string $metaDescription = '',
    ) {}

    public function render(): View
    {
        $storeName = settings('store_name', 'Artisan Bakery');
        $storeLogo = settings('store_logo');

        return view('components.layouts.storefront', [
            'ogStoreName' => $storeName,
            'ogDescription' => settings('store_tagline', "{$storeName} — Fresh baked goods made with love"),
            'ogLogo' => $storeLogo ? asset("storage/{$storeLogo}") : null,
            'storefrontTheme' => settings('storefront_theme', 'classic'),
        ]);
    }
}
