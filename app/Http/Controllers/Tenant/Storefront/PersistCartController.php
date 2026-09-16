<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\PersistCartRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Carts\CartManager;
use App\Services\Orders\ItemPayloadNormalizer;
use Illuminate\Http\JsonResponse;

class PersistCartController extends Controller
{
    public function __invoke(
        PersistCartRequest $request,
        CartManager $manager,
        ItemPayloadNormalizer $itemPayloadNormalizer,
    ): JsonResponse {
        $cart = $manager->currentOrCreate();

        $manager->updateContact(
            $cart,
            $request->filled('customer_email') ? $request->string('customer_email')->toString() : null,
            $request->filled('customer_name') ? $request->string('customer_name')->toString() : null,
        );

        $manager->replaceItems($cart, $itemPayloadNormalizer->products($request->array('items')));

        return ApiResponse::success([
            'cart_token' => $cart->cart_token,
            'item_count' => $cart->items()->count(),
        ], 'Cart saved.');
    }
}
