<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function show(Order $order, Request $request): View
    {
        $order->load(['customer', 'orderItems.product']);
        $storeName = Setting::get('store_name', 'Our Bakery');
        $prefilledRating = $request->query('rating');

        return view('submit-review', compact('order', 'storeName', 'prefilledRating'));
    }

    public function store(Order $order, StoreReviewRequest $request): View
    {
        $validated = $request->validated();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('review-photos', 'public');
        }

        Review::query()->create([
            'customer_name' => $order->customer->name ?? 'Customer',
            'customer_email' => $order->customer->email ?? '',
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => isset($validated['comment']) ? strip_tags($validated['comment']) : null,
            'photo_path' => $photoPath,
            'is_approved' => false,
        ]);

        $storeName = Setting::get('store_name', 'Our Bakery');

        return view('submit-review', [
            'order' => $order,
            'storeName' => $storeName,
            'prefilledRating' => null,
            'success' => true,
        ]);
    }
}
