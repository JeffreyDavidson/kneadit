<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    /**
     * Show the storefront about page.
     */
    public function __invoke(): View
    {
        return view('about');
    }
}
