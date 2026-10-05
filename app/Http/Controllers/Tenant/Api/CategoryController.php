<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        // Loaded here with the storefront's filter: the resource loads any
        // missing relation named by ?include=, which would expose inactive products.
        $categories = Category::query()->active()
            ->orderBy('sort_order')
            ->with([
                'products' => fn (HasMany $q) => $q->where('is_active', true),
            ])
            ->get();

        return CategoryResource::collection($categories);
    }
}
