<?php

namespace App\View\Components\Home;

use App\Models\Customers\CustomerPhoto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class Gallery extends Component
{
    /** @var Collection<int, CustomerPhoto> */
    public Collection $customerPhotos;

    public string $title;

    public string $subtitle;

    /** @param array<string, mixed> $config */
    public function __construct(public array $config = [])
    {
        $this->title = $config['title'] ?? 'From Our Customers';
        $this->subtitle = $config['subtitle'] ?? 'Shared by our community';
        $count = (int) ($config['count'] ?? 6);

        $this->customerPhotos = $this->loadFeaturedPhotos($count);
    }

    public function render(): View
    {
        return view('components.home.gallery');
    }

    /**
     * @return Collection<int, CustomerPhoto>
     */
    private function loadFeaturedPhotos(int $count): Collection
    {
        $cacheKey = 'home_gallery_photos_' . (tenant()?->getTenantKey() ?? 'none') . "_{$count}";

        try {
            return Cache::flexible($cacheKey, [300, 1800], fn () => CustomerPhoto::query()
                ->approved()
                ->featured()
                ->with('product')
                ->latest()
                ->take($count)
                ->get());
        } catch (\Exception) {
            return collect();
        }
    }
}
