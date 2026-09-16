<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Actions\Customers\ToggleCustomerFavorite;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiFavoriteRequest;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\FavoriteToggleResource;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($customer instanceof Customer, 401);

        $favorites = CustomerFavorite::query()
            ->forCustomer($customer->email)
            ->with('product')
            ->get();

        return FavoriteResource::collection($favorites);
    }

    public function store(StoreApiFavoriteRequest $request, ToggleCustomerFavorite $toggleFavorite): FavoriteToggleResource
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($customer instanceof Customer, 401);

        $email = $customer->email;
        $productId = $request->integer('product_id');

        $favorited = $toggleFavorite($email, $productId);

        return new FavoriteToggleResource([
            'customer_email' => $email,
            'product_id' => $productId,
            'favorited' => $favorited,
        ]);
    }
}
