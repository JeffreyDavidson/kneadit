<?php

namespace App\View\Components\Storefront\Home;

use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class Cta extends Component
{
    private const string DEFAULT_IMAGE_URL = 'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=1920&q=80';

    public string $heading;

    public ?string $subtext;

    public string $buttonText;

    public string $href;

    public int $leadTimeHours;

    public string $imageUrl;

    /** @param array<string, mixed> $config */
    public function __construct(public array $config = [])
    {
        $settings = resolve(TenantSettings::class);
        $buttonLink = is_string($config['button_link'] ?? null) ? $config['button_link'] : 'order';

        $this->heading = is_string($config['heading'] ?? null) ? $config['heading'] : 'Treat Yourself Today';
        $this->subtext = is_string($config['subtext'] ?? null) ? $config['subtext'] : null;
        $this->buttonText = is_string($config['button_text'] ?? null) ? $config['button_text'] : 'Start Your Order';
        $this->href = match ($buttonLink) {
            'menu' => route('storefront.menu'),
            'contact' => route('contact.show'),
            default => route('order.create'),
        };
        $this->leadTimeHours = $settings->orders->leadTimeHours;
        $this->imageUrl = $settings->branding->heroImage
            ? Storage::url($settings->branding->heroImage)
            : self::DEFAULT_IMAGE_URL;
    }

    public function render(): View
    {
        return view('components.storefront.home.cta');
    }
}
