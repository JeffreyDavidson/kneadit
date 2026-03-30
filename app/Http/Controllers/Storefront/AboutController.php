<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Queries\StorefrontStatsQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
    /**
     * Show the storefront about page.
     */
    public function __invoke(): View
    {
        $storeName = settings('store_name', 'Our Bakery');
        $tagline = settings('business_tagline');
        $aboutUs = settings('about_us_text');
        $address = settings('store_address');
        $allergyDisclaimer = settings('allergy_disclaimer');
        $socialLinks = json_decode(settings('social_media_links', '{}'), true);
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('about');

        $stats = StorefrontStatsQuery::get();

        return view('about', [
            'storeName' => $storeName,
            'tagline' => $tagline,
            'aboutUs' => $aboutUs,
            'address' => $address,
            'allergyDisclaimer' => $allergyDisclaimer,
            'socialLinks' => $socialLinks,
            'heroImageUrl' => $heroImageUrl,
            'content' => $content,
            'customerCount' => $stats['customer_count'],
            'avgRating' => $stats['avg_rating'],
            'orderCount' => $stats['order_count'],
        ]);
    }
}
