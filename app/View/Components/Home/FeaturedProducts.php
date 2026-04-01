<?php

namespace App\View\Components\Home;

use App\Models\Inventory\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class FeaturedProducts extends Component
{
    /** @var Collection<int, Product> */
    public Collection $featuredProducts;

    public int $count;

    public string $title;

    public string $subtitle;

    /** @param array<string, mixed> $config */
    public function __construct(public array $config = [])
    {
        $this->count = $config['count'] ?? 6;
        $this->title = $config['title'] ?? 'Our Favorites';
        $this->subtitle = $config['subtitle'] ?? 'Freshly made with love';
        $this->featuredProducts = Product::query()
            ->active()
            ->take($this->count)
            ->get();
    }

    public function render(): View
    {
        return view('components.home.featured-products');
    }
}
