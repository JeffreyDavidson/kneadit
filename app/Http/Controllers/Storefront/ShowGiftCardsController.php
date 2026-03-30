<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class ShowGiftCardsController extends Controller
{
    /**
     * Show the gift cards page.
     */
    public function __invoke(): View
    {
        $heroImage = settings('gift_cards_hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
        $content = settingsPageContent('gift_cards');

        return view('gift-cards', [
            'heroImageUrl' => $heroImageUrl,
            'content' => $content,
        ]);
    }
}
