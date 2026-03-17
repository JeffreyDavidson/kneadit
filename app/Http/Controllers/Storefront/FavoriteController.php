<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
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

        $favorites = CustomerFavorite::where('customer_email', $email)
            ->pluck('product_id')
            ->toArray();

        return response()->json(['favorites' => $favorites]);
    }

    /**
     * Toggle a product as a customer favorite.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $existing = CustomerFavorite::where('customer_email', $request->email)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favorited' => false]);
        }

        CustomerFavorite::create([
            'customer_email' => $request->email,
            'product_id' => $request->product_id,
        ]);

        return response()->json(['favorited' => true]);
    }
}
