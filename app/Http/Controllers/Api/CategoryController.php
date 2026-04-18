<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Inventory\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $categories = Category::query()->active()
            ->orderBy('sort_order')
            ->get();

        return CategoryResource::collection($categories);
    }
}
