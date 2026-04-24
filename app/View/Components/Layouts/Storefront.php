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
        $settings = resolve(TenantSettings::class);

        return view('components.layouts.storefront', [
            'settings' => $settings,
            'ogStoreName' => $settings->store->name,
            'ogDescription' => $settings->defaultTagline(),
            'ogLogo' => $settings->store->logoUrl(),
            'storefrontTheme' => $settings->branding->storefrontTheme,
        ]);
    }
}
