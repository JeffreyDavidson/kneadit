<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    /**
     * Show the storefront about page.
     */
    public function __invoke(): View
    {
        return view('about', [
            'customerCount' => Customer::query()->count(),
            'avgRating' => Review::query()->where('is_approved', true)->avg('rating'),
            'orderCount' => Order::query()->count(),
        ]);
    }
}
