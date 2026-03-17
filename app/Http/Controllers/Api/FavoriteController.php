<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $productIds = CustomerFavorite::forCustomer($request->input('email'))
            ->pluck('product_id');

        return response()->json([
            'data' => $productIds,
            'message' => 'Favorites retrieved successfully.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $favorited = CustomerFavorite::toggle($validated['email'], $validated['product_id']);

        return response()->json([
            'data' => ['favorited' => $favorited],
            'message' => $favorited ? 'Added to favorites.' : 'Removed from favorites.',
        ]);
    }
}
