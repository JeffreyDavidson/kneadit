<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Orders\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

class RecoverCartController extends Controller
{
    public function __invoke(string $cart_token): RedirectResponse
    {
        $cart = Cart::query()
            ->forToken($cart_token)
            ->notConverted()
            ->first();

        if (! $cart) {
            return to_route('order.create')
                ->withErrors(['cart' => 'This cart is no longer available.']);
        }

        Cookie::queue('cart_token', $cart->cart_token, 60 * 24 * 30, httpOnly: false);

        return to_route('order.create')
            ->with('success', 'Welcome back — your cart is ready.');
    }
}
