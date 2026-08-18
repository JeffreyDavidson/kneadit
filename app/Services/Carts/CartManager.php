<?php

namespace App\Services\Carts;

use App\Models\Inventory\Product;
use App\Models\Orders\Cart;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Resolves the customer's cart from the signed cart_token cookie and
 * provides write operations (replace items, update contact, touch).
 *
 * Cart_token cookies live 30 days. When a customer's cart is created,
 * the token is queued onto the response so subsequent requests can
 * reattach to the same cart row.
 */
class CartManager
{
    private const string COOKIE_NAME = 'cart_token';

    private const int COOKIE_TTL_MINUTES = 60 * 24 * 30; // 30 days

    public function current(): ?Cart
    {
        $token = request()->cookie(self::COOKIE_NAME);

        if (! is_string($token) || $token === '') {
            return null;
        }

        return Cart::query()
            ->forToken($token)
            ->notConverted()
            ->first();
    }

    public function currentOrCreate(): Cart
    {
        $cart = $this->current();
        if ($cart !== null) {
            return $cart;
        }

        $token = (string) Str::ulid();

        $cart = Cart::query()->create([
            'cart_token' => $token,
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes(self::COOKIE_TTL_MINUTES),
        ]);

        Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_TTL_MINUTES, httpOnly: false);

        return $cart;
    }

    /**
     * Replace the cart's items wholesale. The Alpine cart is the source
     * of truth on the client; we mirror it on the server.
     *
     * @param array<int, array{product_id: int, quantity: int}> $items
     */
    public function replaceItems(Cart $cart, array $items): void
    {
        DB::transaction(function () use ($cart, $items): void {
            $cart->items()->delete();

            if ($items === []) {
                return;
            }

            $productIds = array_column($items, 'product_id');
            $products = Product::query()->findMany($productIds)->keyBy('id');

            $rows = [];
            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    continue;
                }

                $quantity = max(1, (int) $item['quantity']);
                $unitPriceCents = $product->price?->cents() ?? 0;

                $rows[] = [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPriceCents,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($rows !== []) {
                $cart->items()->getRelated()->newQuery()->insert($rows);
            }
        });

        $this->touch($cart);
    }

    public function updateContact(Cart $cart, ?string $email, ?string $name): void
    {
        $email = $email !== null && trim($email) !== '' ? trim($email) : null;
        $name = $name !== null && trim($name) !== '' ? trim($name) : null;

        if ($cart->customer_email === $email && $cart->customer_name === $name) {
            return;
        }

        $cart->forceFill([
            'customer_email' => $email,
            'customer_name' => $name,
        ])->save();

        $this->touch($cart);
    }

    public function touch(Cart $cart): void
    {
        $cart->forceFill(['last_activity_at' => now()])->save();
    }
}
