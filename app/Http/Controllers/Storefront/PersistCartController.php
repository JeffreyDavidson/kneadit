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
            $request->filled('customer_email') ? $request->string('customer_email')->toString() : null,
            $request->filled('customer_name') ? $request->string('customer_name')->toString() : null,
        );

        $manager->replaceItems($cart, $this->items($request->array('items')));

        return ApiResponse::success([
            'cart_token' => $cart->cart_token,
            'item_count' => $cart->items()->count(),
        ], 'Cart saved.');
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, array{product_id: int, quantity: int}>
     */
    private function items(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? null;

            if (is_int($productId) && is_int($quantity)) {
                $normalized[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ];
            }
        }

        return $normalized;
    }
}
