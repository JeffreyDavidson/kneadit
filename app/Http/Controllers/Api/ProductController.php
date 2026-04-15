<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexProductsRequest;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Models\Inventory\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __invoke(IndexProductsRequest $request): JsonResponse
    {
        $query = Product::query()->active()->with('category');

        if ($request->has('category')) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $request->validated('category')));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        return ApiResponse::success(ProductResource::collection($query->get()), 'Products retrieved successfully.');
    }
}
