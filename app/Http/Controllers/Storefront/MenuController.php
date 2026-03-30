<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * Show the storefront menu page.
     */
    public function __invoke(): View
    {
        $categories = Category::query()->active()
            ->with(['products' => fn (HasMany $q) => $q->where('is_active', true)->orderBy('name'), 'products.seasonalItems'])
            ->orderBy('sort_order')
            ->get();

        $storeName = settings('store_name', 'Our Bakery');
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('menu');
        $heroEyebrow = str_replace('{{store_name}}', $storeName, $content['hero_eyebrow'] ?? $storeName);
        $ctaDesc = str_replace('{{lead_time}}', settings('order_lead_time_hours', '24'), $content['cta_description'] ?? 'All orders need ' . settings('order_lead_time_hours', '24') . ' hours notice. Place yours now.');

        return view('menu', compact('categories', 'storeName', 'heroImageUrl', 'content', 'heroEyebrow', 'ctaDesc'));
    }
}
