<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Engagement\Review;
use App\Services\Settings\TenantSettings;
use App\ViewModels\Storefront\ReviewsPageViewModel;
use Illuminate\Contracts\View\View;

class ReviewsIndexController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        $reviews = Review::query()->forDisplay()->paginate(12);
        $stats = Review::query()->statistics();
        $starCounts = Review::query()->ratingBreakdown();

        return view('storefront.reviews', [
            'storefrontTheme' => (string) settings('storefront_theme', 'classic'),
            'vm' => new ReviewsPageViewModel(
                reviews: $reviews,
                stats: $stats,
                starCounts: $starCounts,
                settings: $settings,
                content: settingsPageContent('reviews'),
            ),
        ]);
    }
}
