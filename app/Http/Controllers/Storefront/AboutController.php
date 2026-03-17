<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    /**
     * Show the storefront about page.
     */
    public function __invoke()
    {
        return view('about');
    }
}
