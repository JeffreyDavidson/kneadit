<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\ToggleCustomerFavorite;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiFavoriteRequest;
use App\Http\Responses\ApiResponse;
use App\Models\CustomerFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $productIds = CustomerFavorite::query()->forCustomer($request->string('email'))
            ->pluck('product_id');

        return ApiResponse::success($productIds, 'Favorites retrieved successfully.');
    }

    public function store(StoreApiFavoriteRequest $request, ToggleCustomerFavorite $toggleFavorite): JsonResponse
    {
        $validated = $request->validated();

        $favorited = $toggleFavorite($validated['email'], $validated['product_id']);

        return ApiResponse::success([
            'favorited' => $favorited,
        ], $favorited ? 'Added to favorites.' : 'Removed from favorites.');
    }
}
