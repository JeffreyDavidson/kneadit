<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexReviewsRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Engagement\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function index(IndexReviewsRequest $request): AnonymousResourceCollection
    {
        // Constrained so ?include=product can't expose an inactive product.
        $query = Review::query()->approved()->with([
            'product' => fn (BelongsTo $q) => $q->where('is_active', true),
        ]);

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        return ReviewResource::collection($query->latest()->get());
    }
}
