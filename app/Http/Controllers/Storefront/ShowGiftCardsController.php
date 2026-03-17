<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;

class ShowGiftCardsController extends Controller
{
    /**
     * Show the gift cards page.
     */
    public function __invoke()
    {
        return view('gift-cards');
    }
}
