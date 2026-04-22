<?php

namespace App\Services\Orders;

use App\Models\Orders\Order;

/**
 * Tracks which orders the current session has been granted access to.
 *
 * Granting happens automatically after order placement, Stripe redirect-back,
 * and successful email-based order tracking. External access (e.g. opening
 * an order link in a different browser) requires explicit email verification
 * via VerifyOrderAccessController, after which the order is added here.
 *
 * Authenticated customers viewing their own orders bypass the session gate
 * entirely — the customer_id match is proof enough.
 */
final class OrderAccessGuard
{
    private const SESSION_KEY = 'verified_order_numbers';

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

        if ($customer && $order->customer_id === $customer->getKey()) {
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

        return is_array($verified) ? array_values(array_filter($verified, 'is_string')) : [];
    }
}
