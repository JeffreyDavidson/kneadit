<?php

namespace App\Pipes\Orders;

use App\Models\Orders\Cart;
use Closure;

/**
 * Marks the customer's cart as converted once the order is persisted.
 * Uses the cart_token cookie to find the matching cart. Skips silently
 * if there's no cart (customer went straight to checkout or cookie
 * was never set).
 */
class MarkCartConverted
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $token = request()->cookie('cart_token');

        if (! is_string($token) || $token === '') {
            return $next($payload);
        }

        Cart::query()
            ->forToken($token)
            ->notConverted()
            ->update(['converted_at' => now()]);

        return $next($payload);
    }
}
