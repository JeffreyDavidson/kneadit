<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\PersistCartRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Carts\CartManager;
use Illuminate\Http\JsonResponse;

class PersistCartController extends Controller
{
    public function __invoke(PersistCartRequest $request, CartManager $manager): JsonResponse
    {
        $cart = $manager->currentOrCreate();

        $manager->updateContact(
            $cart,
            $request->validated('customer_email'),
            $request->validated('customer_name'),
        );

        $manager->replaceItems($cart, $request->validated('items') ?? []);

        return ApiResponse::success([
            'cart_token' => $cart->cart_token,
            'item_count' => $cart->items()->count(),
        ], 'Cart saved.');
    }
}
