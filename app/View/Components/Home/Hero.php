<?php

namespace App\View\Components\Home;

use App\Models\Customer;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class Hero extends Component
{
    public string $storeName;

    public ?string $tagline;

    public ?string $aboutUs;

    public string $heroStyle;

    public string $heroImageUrl;

    public int $customerCount;

    public ?float $avgRating;

    public ?Review $topReview;

    public function __construct()
    {
        $this->storeName = settings('store_name', 'Our Bakery');
        $this->tagline = settings('business_tagline');
        $this->aboutUs = settings('about_us_text');
        $this->heroStyle = settings('hero_style', 'split');

        $heroImage = settings('hero_image');
        $this->heroImageUrl = $heroImage
            ? Storage::url($heroImage)
            : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

        $this->customerCount = Customer::query()->count();
        $avg = Review::query()->approved()->avg('rating');
        $this->avgRating = $avg !== null ? (float) $avg : null;
        $this->topReview = Review::query()
            ->approved()
            ->where('rating', '>=', 4)
            ->latest()
            ->first();
    }

    public function render(): View
    {
        return view('components.home.hero');
    }
}
