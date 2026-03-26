<?php

namespace App\View\Components\Home;

use App\Models\CustomerPhoto;
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

        try {
            $this->customerPhotos = CustomerPhoto::query()
                ->approved()
                ->featured()
                ->with('product')
                ->latest()
                ->take($count)
                ->get();
        } catch (\Exception) {
            $this->customerPhotos = collect();
        }
    }

    public function render(): View
    {
        return view('components.home.gallery');
    }
}
