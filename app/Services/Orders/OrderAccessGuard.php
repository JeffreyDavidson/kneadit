<?php

namespace App\Services\Orders;

use App\Models\Orders\Order;
use Illuminate\Contracts\Auth\MustVerifyEmail;

/**
 * Tracks which orders the current session has been granted access to.
 *
 * Granting happens automatically after order placement, Stripe redirect-back,
 * and opening the signed tracking link emailed to the customer. External access (e.g. opening
 * an order link in a different browser) requires explicit email verification
 * via VerifyOrderAccessController, after which the order is added here.
 *
 * Authenticated customers with a verified email viewing their own orders
 * bypass the session gate — the customer_id match is proof enough. An
 * unverified customer account gets no access from the match alone.
 */
final class OrderAccessGuard
{
    private const string SESSION_KEY = 'verified_order_numbers';

    /**
     * Mark this order as accessible to the current session.
     */
    public static function grant(Order $order): void
    {
        $verified = self::current();

        if (! in_array($order->order_number, $verified, true)) {
            $verified[] = $order->order_number;
            session([self::SESSION_KEY => $verified]);
        }
    }

    /**
     * Has the current session (or the authenticated customer) earned
     * access to this order?
     */
    public static function canAccess(Order $order): bool
    {
        $customer = auth('customer')->user();

        if ($customer instanceof MustVerifyEmail && $customer->hasVerifiedEmail() && $order->customer_id === $customer->getKey()) {
            return true;
        }

        return in_array($order->order_number, self::current(), true);
    }

    /**
     * @return array<int, string>
     */
    private static function current(): array
    {
        $verified = session(self::SESSION_KEY, []);

        return is_array($verified) ? array_values(array_filter($verified, is_string(...))) : [];
    }
}
