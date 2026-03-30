<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Http\Responses\ApiResponse;
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

        return ApiResponse::success(ReviewResource::collection($query->latest()->get()), 'Reviews retrieved successfully.');
    }

    public function store(StoreApiReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $review = Review::query()->create([
            ...$validated,
            'is_approved' => false,
        ]);

        return ApiResponse::created([
            'id' => $review->id,
        ], 'Review submitted and pending approval.');
    }
}
