<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\ToggleCustomerFavorite;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiFavoriteRequest;
use App\Models\CustomerFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {

        $productIds = CustomerFavorite::forCustomer((string) $request->input('email'))
            ->pluck('product_id');

        return response()->json([
            'data' => $productIds,
            'message' => 'Favorites retrieved successfully.',
        ]);
    }

    public function store(StoreApiFavoriteRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $favorited = app(ToggleCustomerFavorite::class)((string) $validated['email'], (int) $validated['product_id']);

        return response()->json([
            'data' => ['favorited' => $favorited],
            'message' => $favorited ? 'Added to favorites.' : 'Removed from favorites.',
        ]);
    }
}
