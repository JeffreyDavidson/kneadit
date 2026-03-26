<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\CreateReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function show(Order $order, Request $request): View
    {
        $order->load(['customer', 'orderItems.product']);
        $storeName = settings('store_name', 'Our Bakery');
        $prefilledRating = $request->query('rating');

        return view('submit-review', compact('order', 'storeName', 'prefilledRating'));
    }

    public function store(Order $order, StoreReviewRequest $request, CreateReview $createReview): View
    {
        $validated = $request->validated();

        $createReview(
            order: $order,
            rating: (int) $validated['rating'],
            comment: $validated['comment'] ?? null,
            photo: $request->file('photo'),
        );

        $storeName = settings('store_name', 'Our Bakery');

        return view('submit-review', [
            'order' => $order,
            'storeName' => $storeName,
            'prefilledRating' => null,
            'success' => true,
        ]);
    }
}
