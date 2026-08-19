<?php

namespace App\Builders\Orders;

use App\Models\Orders\Cart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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

    public function abandonedBefore(Carbon $cutoff): self
    {
        $this->whereNotNull('customer_email')
            ->whereNull('recovery_sent_at')
            ->notConverted()
            ->where('last_activity_at', '<=', $cutoff);

        return $this;
    }

    public function withRecoverableItems(): static
    {
        $this->whereHas('items')->with('items.product');

        return $this;
    }
}
