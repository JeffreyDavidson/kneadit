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
        $this->title = $config['title'] ?? 'From Our Customers';
        $this->subtitle = $config['subtitle'] ?? 'Shared by our community';
        $count = $config['count'] ?? 6;

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
