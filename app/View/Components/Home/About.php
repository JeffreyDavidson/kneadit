<?php

namespace App\View\Components\Home;

use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class About extends Component
{
    public ?string $aboutUs;

    public string $storeName;

    public ?string $storePhoto;

    public function __construct()
    {
        $settings = resolve(TenantSettings::class);

        $this->aboutUs = $settings->branding->aboutUsText;
        $this->storeName = $settings->store->name;
        $this->storePhoto = $settings->store->photo;
    }

    public function render(): View
    {
        return view('components.home.about');
    }
}
