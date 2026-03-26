<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiReviewRequest;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()->approved()->with('product');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $reviews = $query->latest()->get()->map(fn (Review $r) => [
            'customer_name' => $r->customer_name,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'product_name' => $r->product?->name,
            'created_at' => $r->created_at?->toISOString(),
        ]);

        return response()->json([
            'data' => $reviews,
            'message' => 'Reviews retrieved successfully.',
        ]);
    }

    public function store(StoreApiReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['comment'] = strip_tags($validated['comment']);

        $review = Review::query()->create([
            ...$validated,
            'is_approved' => false,
        ]);

        return response()->json([
            'data' => ['id' => $review->id],
            'message' => 'Review submitted and pending approval.',
        ], 201);
    }
}
