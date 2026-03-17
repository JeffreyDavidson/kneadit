<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['products' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'description' => $c->description,
                'sort_order' => $c->sort_order,
                'products' => $c->products->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'description' => $p->description,
                    'price' => $p->price,
                    'image' => $p->image,
                    'is_featured' => $p->is_featured,
                ]),
            ]);

        return response()->json([
            'data' => $categories,
            'message' => 'Menu retrieved successfully.',
        ]);
    }
}
