<?php

namespace App\View\Components\Home;

use App\Models\Customers\CustomerPhoto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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
        $this->title = is_string($config['title'] ?? null) ? $config['title'] : 'From Our Customers';
        $this->subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : 'Shared by our community';
        $count = is_int($config['count'] ?? null) ? $config['count'] : 6;

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
        // Not cached — Eloquent collections don't roundtrip through Redis cleanly
        // because config('cache.serializable_classes') is false (security default).
        // unserialize() returns __PHP_Incomplete_Class and the strict return type
        // here errors out into a 500. Same pattern as Hero::topReview. The query
        // is two indexed lookups; not worth the operational fragility.
        return CustomerPhoto::query()
            ->approved()
            ->featured()
            ->with('product')
            ->latest()
            ->take($count)
            ->get();
    }
}
