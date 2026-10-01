<?php

declare(strict_types=1);

namespace App\Builders\Orders;

use App\Models\Customers\Customer;
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

    public function notConverted(): static
    {
        $this->whereNull('converted_at');

        return $this;
    }
}
