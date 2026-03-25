<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiFavoriteRequest;
use App\Models\CustomerFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get customer favorites.
     */
    public function show(Request $request): JsonResponse
    {
        $email = $request->query('email');

        if (! $email) {
            return response()->json(['favorites' => []]);
        }

        $favorites = CustomerFavorite::query()->where('customer_email', $email)
            ->pluck('product_id')
            ->toArray();

        return response()->json(['favorites' => $favorites]);
    }

    /**
     * Toggle a product as a customer favorite.
     */
    public function store(StoreApiFavoriteRequest $request): JsonResponse
    {

        $existing = CustomerFavorite::query()->where('customer_email', $request->email)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favorited' => false]);
        }

        CustomerFavorite::query()->create([
            'customer_email' => $request->email,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['favorited' => true]);
    }
}
