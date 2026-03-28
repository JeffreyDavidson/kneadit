<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class AboutController extends Controller
{
    /**
     * Show the storefront about page.
     */
    public function __invoke(): View
    {
        return view('about', [
            'customerCount' => Cache::remember('about_customer_count', 3600, fn () => Customer::query()->count()),
            'avgRating' => Cache::remember('about_avg_rating', 3600, fn () => Review::query()->where('is_approved', true)->avg('rating')),
            'orderCount' => Cache::remember('about_order_count', 3600, fn () => Order::query()->count()),
        ]);
    }
}
