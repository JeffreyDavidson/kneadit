<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = Product::query()->active()->with('category');

        if ($request->has('category')) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $request->input('category')));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        return ApiResponse::success(ProductResource::collection($query->get()), 'Products retrieved successfully.');
    }
}
