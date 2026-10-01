<?php

namespace App\Services\Orders;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;

/**
 * Determines whether an order can still be modified by the customer.
 *
 * Eligibility requires the modification window setting to be enabled (> 0),
 * the order to still be pending and unpaid with no card checkout open, and the
 * elapsed time since placement to be within the configured window.
 */
final readonly class OrderModificationGuard
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    public function canModify(Order $order): bool
    {
        $window = $this->settings->orders->modificationWindowMinutes;

        if ($window <= 0) {
            return false;
        }

        if ($order->status !== OrderStatus::Pending) {
            return false;
        }

        if ($order->payment_status !== PaymentStatus::Unpaid) {
            return false;
        }

        if ($this->hasOpenCheckout($order)) {
            return false;
        }

        if ($order->created_at === null) {
            return false;
        }

        return $order->created_at->diffInMinutes(now(), false) < $window;
    }

    /**
     * An unpaid order with a Stripe checkout session may still be paid at the session's amount,
     * so it cannot be edited until that payment is settled.
     */
    public function hasOpenCheckout(Order $order): bool
    {
        return $order->stripe_checkout_session_id !== null
            && $order->payment_status === PaymentStatus::Unpaid;
    }

    public function minutesRemaining(Order $order): int
    {
        $window = $this->settings->orders->modificationWindowMinutes;

        if ($window <= 0 || $order->created_at === null) {
            return 0;
        }

        $elapsed = (int) $order->created_at->diffInMinutes(now(), false);

        return max(0, $window - $elapsed);
    }
}
