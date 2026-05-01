<?php

namespace App\Builders\Orders;

use App\Models\Orders\Cart;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Cart> */
class CartQueryBuilder extends Builder
{
    public function forToken(string $token): static
    {
        $this->where('cart_token', $token);

        return $this;
    }

    public function notConverted(): static
    {
        $this->whereNull('converted_at');

        return $this;
    }
}
