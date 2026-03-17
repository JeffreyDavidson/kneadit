<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = Product::where('is_active', true)->with('category');

        if ($request->has('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->input('category')));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $products = $query->get()->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'description' => $p->description,
            'price' => $p->price,
            'image' => $p->image,
            'category_id' => $p->category_id,
            'category_name' => $p->category?->name,
            'is_featured' => $p->is_featured,
        ]);

        return response()->json([
            'data' => $products,
            'message' => 'Products retrieved successfully.',
        ]);
    }
}
