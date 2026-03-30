<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $categories = Category::query()->active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'description', 'sort_order']);

        return ApiResponse::success($categories, 'Categories retrieved successfully.');
    }
}
