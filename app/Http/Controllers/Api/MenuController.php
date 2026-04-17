<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $categories = Category::query()->active()
            ->orderBy('sort_order')
            ->with([
                'products' => function (HasMany $q): void {
                    $q->where('is_active', true);
                },
            ])
            ->get();

        return ApiResponse::success(CategoryResource::collection($categories), 'Menu retrieved successfully.');
    }
}
