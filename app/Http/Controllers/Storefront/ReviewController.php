<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Customers\CreateReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function show(Order $order, Request $request): View
    {
        $order->load(['customer', 'orderItems.product']);
        $storeName = settings('store_name', 'Our Bakery');
        $prefilledRating = $request->query('rating');

        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

        $content = settingsPageContent('submit_review');
        $ratingDescriptions = $content['rating_descriptions'] ?? ['', 'Could be better', 'It was okay', 'Pretty good!', 'Really great!', 'Absolutely amazing!'];

        return view('submit-review', [
            'order' => $order,
            'storeName' => $storeName,
            'prefilledRating' => $prefilledRating,
            'heroImageUrl' => $heroImageUrl,
            'content' => $content,
            'ratingDescriptions' => $ratingDescriptions,
        ]);
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

        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

        $content = settingsPageContent('submit_review');
        $ratingDescriptions = $content['rating_descriptions'] ?? ['', 'Could be better', 'It was okay', 'Pretty good!', 'Really great!', 'Absolutely amazing!'];

        return view('submit-review', [
            'order' => $order,
            'storeName' => $storeName,
            'prefilledRating' => null,
            'heroImageUrl' => $heroImageUrl,
            'content' => $content,
            'ratingDescriptions' => $ratingDescriptions,
            'success' => true,
        ]);
    }
}
