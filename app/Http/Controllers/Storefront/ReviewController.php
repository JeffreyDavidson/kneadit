<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function submitReview(Order $order, Request $request)
    {
        $order->load(['customer', 'orderItems.product']);
        $storeName = Setting::get('store_name', 'Our Bakery');
        $prefilledRating = $request->query('rating');

        return view('submit-review', compact('order', 'storeName', 'prefilledRating'));
    }

    public function storeReview(Order $order, Request $request)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('review-photos', 'public');
        }

        Review::create([
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
