<?php

namespace App\View\Components\Home;

use App\Models\Customers\Customer;
use App\Models\Engagement\Review;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class Hero extends Component
{
    public string $storeName;

    public ?string $tagline;

    public ?string $aboutUs;

    public string $heroStyle;

    public string $heroImageUrl;

    public ?string $heroTagline;

    public string $primaryCtaText;

    public string $secondaryCtaText;

    public int $customerCount;

    public ?float $avgRating;

    public ?Review $topReview;

    public function __construct()
    {
        $settings = app(TenantSettings::class);
        $branding = $settings->branding;

        $this->storeName = $settings->store->name;
        $this->tagline = $branding->businessTagline;
        $this->aboutUs = $branding->aboutUsText;
        $this->heroStyle = $branding->heroStyle;
        $this->heroImageUrl = $branding->heroImageUrl();
        $this->heroTagline = $branding->heroTagline;
        $this->primaryCtaText = $branding->heroPrimaryCtaText;
        $this->secondaryCtaText = $branding->heroSecondaryCtaText;

        $this->customerCount = Cache::flexible('hero_customer_count', [3600, 7200], fn () => Customer::query()->count());
        $this->avgRating = Cache::flexible('hero_avg_rating', [3600, 7200], function () {
            $avg = Review::query()->approved()->avg('rating');

            return $avg !== null ? (float) $avg : null;
        });
        $this->topReview = Cache::flexible('hero_top_review', [3600, 7200], fn () => Review::query()
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
