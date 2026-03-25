<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ShowGiftCardsController extends Controller
{
    /**
     * Show the gift cards page.
     */
    public function __invoke(): View
    {
        return view('gift-cards');
    }
}
