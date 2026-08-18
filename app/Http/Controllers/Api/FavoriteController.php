<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\ToggleCustomerFavorite;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexFavoritesRequest;
use App\Http\Requests\Api\StoreApiFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\FavoriteToggleResource;
use App\Models\Customers\CustomerFavorite;
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

    public function store(StoreApiFavoriteRequest $request, ToggleCustomerFavorite $toggleFavorite): FavoriteToggleResource
    {
        $email = $request->validated('email');
        $productId = (int) $request->validated('product_id');

        $favorited = $toggleFavorite($email, $productId);

        return new FavoriteToggleResource([
            'customer_email' => $email,
            'product_id' => $productId,
            'favorited' => $favorited,
        ]);
    }
}
