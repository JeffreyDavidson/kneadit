<?php

namespace App\View\Components\Home;

use App\Models\Inventory\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Categories extends Component
{
    /** @var Collection<int, Category> */
    public Collection $categories;

    public string $title;

    /** @param array<string, mixed> $config */
    public function __construct(public array $config = [])
    {
        $this->title = $config['title'] ?? 'What We Bake';
        $this->categories = Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('components.home.categories');
    }
}
