<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\SubmitApiReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexReviewsRequest;
use App\Http\Requests\Api\StoreApiReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Engagement\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function index(IndexReviewsRequest $request): AnonymousResourceCollection
    {
        $query = Review::query()->approved()->with('product');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        return ReviewResource::collection($query->latest()->get());
    }

    public function store(StoreApiReviewRequest $request, SubmitApiReview $submitReview): JsonResponse
    {
        $review = $submitReview($request->validated());

        return ReviewResource::make($review)->response()->setStatusCode(201);
    }
}
