<?php

namespace App\View\Components\Storefront\Home;

use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Social extends Component
{
    /** @var array<string, string> */
    public array $socialLinks;

    public string $storeName;

    public function __construct()
    {
        $settings = resolve(TenantSettings::class);

        $this->socialLinks = $settings->homepage->socialMediaLinks;
        $this->storeName = $settings->store->name;
    }

    public function render(): View
    {
        return view('components.storefront.home.social');
    }
}
