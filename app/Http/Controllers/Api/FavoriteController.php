<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\ToggleCustomerFavorite;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexFavoritesRequest;
use App\Http\Requests\Api\StoreApiFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Http\Responses\ApiResponse;
use App\Models\Customers\CustomerFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function index(IndexFavoritesRequest $request): AnonymousResourceCollection
    {
        $favorites = CustomerFavorite::query()
            ->forCustomer($request->validated('email'))
            ->with('product')
            ->get();

        return FavoriteResource::collection($favorites);
    }

    public function store(StoreApiFavoriteRequest $request, ToggleCustomerFavorite $toggleFavorite): JsonResponse
    {
        $favorited = $toggleFavorite($request->validated('email'), $request->validated('product_id'));

        return ApiResponse::success([
            'favorited' => $favorited,
        ], $favorited ? 'Added to favorites.' : 'Removed from favorites.');
    }
}
