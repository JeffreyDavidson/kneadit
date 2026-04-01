<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\ToggleCustomerFavorite;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexFavoritesRequest;
use App\Http\Requests\Api\StoreApiFavoriteRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Customers\CustomerFavorite;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    public function index(IndexFavoritesRequest $request): JsonResponse
    {
        $productIds = CustomerFavorite::query()->forCustomer($request->validated('email'))
            ->pluck('product_id');

        return ApiResponse::success($productIds, 'Favorites retrieved successfully.');
    }

    public function store(StoreApiFavoriteRequest $request, ToggleCustomerFavorite $toggleFavorite): JsonResponse
    {
        $favorited = $toggleFavorite($request->validated('email'), $request->validated('product_id'));

        return ApiResponse::success([
            'favorited' => $favorited,
        ], $favorited ? 'Added to favorites.' : 'Removed from favorites.');
    }
}
