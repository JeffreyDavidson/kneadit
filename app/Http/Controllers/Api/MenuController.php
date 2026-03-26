<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $categories = Category::query()->active()
            ->orderBy('sort_order')
            ->with(['products' => fn (Builder $q) => $q->where('is_active', true)])
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
            'message' => 'Menu retrieved successfully.',
        ]);
    }
}
