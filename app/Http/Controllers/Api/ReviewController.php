<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::where('is_approved', true)->with('product');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $reviews = $query->latest()->get()->map(fn (Review $r) => [
            'customer_name' => $r->customer_name,
            'rating' => $r->rating,
            'comment' => $r->comment,
            'product_name' => $r->product?->name,
            'created_at' => $r->created_at->toISOString(),
        ]);

        return response()->json([
            'data' => $reviews,
            'message' => 'Reviews retrieved successfully.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $validated['comment'] = strip_tags($validated['comment']);

        $review = Review::create([
            ...$validated,
            'is_approved' => false,
        ]);

        return response()->json([
            'data' => ['id' => $review->id],
            'message' => 'Review submitted and pending approval.',
        ], 201);
    }
}
