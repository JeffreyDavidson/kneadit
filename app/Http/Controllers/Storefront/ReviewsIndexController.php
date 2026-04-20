<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\Settings\TenantSettings;
use App\ViewModels\Storefront\ReviewsPageViewModel;
use Illuminate\Contracts\View\View;

class ReviewsIndexController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        return view('storefront.reviews', [
            'vm' => ReviewsPageViewModel::build($settings),
        ]);
    }
}
