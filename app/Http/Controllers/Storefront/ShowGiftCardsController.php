<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ShowGiftCardsController extends Controller
{
    /**
     * Show the gift cards page.
     */
    public function __invoke(TenantSettings $settings): View
    {
        $content = settingsPageContent('gift_cards');

        return view('storefront.gift-cards', [
            'settings' => $settings,
            'content' => $content,
        ]);
    }
}
