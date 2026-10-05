<?php

declare(strict_types=1);

namespace App\Builders\Orders;

use App\Models\Customers\Customer;
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

    /**
     * Carts whose email belongs to a customer who can still receive marketing
     * email. A cart with no matching customer is left out: without a customer
     * record there is nothing to carry an opt-out.
     */
    public function forSubscribedCustomers(): static
    {
        $this->whereIn('customer_email', Customer::query()->subscribedToMarketing()->select('email'));

        return $this;
    }

    /** Carts touched at or after the given moment. */
    public function activeSince(Carbon $since): static
    {
        $this->where('last_activity_at', '>=', $since);

        return $this;
    }

    /** Carts that have not passed their expiry (a cart with no expiry never does). */
    public function notExpired(): static
    {
        $this->where(function (self $query): void {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });

        return $this;
    }

    public function notConverted(): static
    {
        $this->whereNull('converted_at');

        return $this;
    }
}
