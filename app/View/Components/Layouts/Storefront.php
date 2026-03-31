<?php

namespace App\View\Components\Layouts;

use App\Services\Settings\TenantSettings;
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
        $settings = app(TenantSettings::class);

        return view('components.layouts.storefront', [
            'ogStoreName' => $settings->storeName,
            'ogDescription' => $settings->defaultTagline(),
            'ogLogo' => $settings->storeLogoUrl(),
            'storefrontTheme' => $settings->storefrontTheme,
        ]);
    }
}
