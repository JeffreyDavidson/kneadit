<?php

namespace App\View\Components\Layouts;

use App\Enums\Storefront\StorefrontPage;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Storefront extends Component
{
    /**
     * @param  ?string  $pageTitle  The page's own name (a blog post's title); the bakery name is added after it.
     */
    public function __construct(
        public ?string $title = null,
        public ?string $metaDescription = null,
        public ?string $pageTitle = null,
    ) {}

    public function render(): View
    {
        $settings = resolve(TenantSettings::class);
        $storeName = $settings->store->name;
        $page = StorefrontPage::forRoute(request()->route()?->getName());

        // A page can name itself; every other page is titled from the route it was served on.
        $this->title ??= $this->pageTitle !== null
            ? "{$this->pageTitle} · {$storeName}"
            : $page?->title($storeName);
        $this->metaDescription ??= $page?->description($storeName);

        return view('components.layouts.storefront', [
            'settings' => $settings,
            'ogStoreName' => $storeName,
            'ogDescription' => $settings->defaultTagline(),
            'ogLogo' => $settings->store->logoUrl(),
            'storefrontTheme' => $settings->branding->storefrontTheme,
        ]);
    }
}
