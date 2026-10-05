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
     * Carts a recovery email may go to: the cart of a signed-in customer, or a
     * guest cart whose email belongs to a customer who verified it, and in both
     * cases only a customer who can still receive marketing email. A guest cart
     * typed with an address nobody has proved they own is left out, otherwise
     * anyone could make us email a stranger.
     */
    public function forRecoverableCustomers(): static
    {
        $this->where(function (self $query): void {
            $query
                ->whereIn('customer_id', Customer::query()->subscribedToMarketing()->select('id'))
                ->orWhere(function (self $guest): void {
                    $guest
                        ->whereNull('customer_id')
                        ->whereIn('customer_email', Customer::query()->subscribedToMarketing()->emailVerified()->select('email'));
                });
        });

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
