<?php

namespace App\View\Components\Home;

use App\Models\Customer;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
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

        $this->customerCount = Cache::remember('hero_customer_count', 3600, fn () => Customer::query()->count());
        $this->avgRating = Cache::remember('hero_avg_rating', 3600, function () {
            $avg = Review::query()->approved()->avg('rating');

            return $avg !== null ? (float) $avg : null;
        });
        $this->topReview = Cache::remember('hero_top_review', 3600, fn () => Review::query()
            ->approved()
            ->where('rating', '>=', 4)
            ->latest()
            ->first());
    }

    public function render(): View
    {
        return view('components.home.hero');
    }
}
