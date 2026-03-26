<?php

namespace App\View\Components\Home;

use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Reviews extends Component
{
    /** @var Collection<int, Review> */
    public Collection $reviews;

    public ?Review $featuredReview;

    public string $title;

    /** @param array<string, mixed> $config */
    public function __construct(public array $config = [])
    {
        $count = $config['count'] ?? 3;
        $this->title = $config['title'] ?? 'Kind Words';
        $this->reviews = Review::query()
            ->approved()
            ->latest()
            ->take(max($count, 3))
            ->get();
        $this->featuredReview = $this->reviews->first();
    }

    public function render(): View
    {
        return view('components.home.reviews');
    }
}
