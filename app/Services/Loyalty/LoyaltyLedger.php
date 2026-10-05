<?php

namespace App\Services\Loyalty;

use App\Enums\Engagement\LoyaltyPointType;
use App\Enums\Orders\PaymentStatus;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;

class LoyaltyLedger
{
    public function __construct(
        private readonly TenantSettings $settings,
        private readonly CustomerLoyalty $customerLoyalty,
    ) {}

    /**
     * Award loyalty points for a delivered order.
     *
     * Idempotent and race-safe: relies on the unique index on
     * loyalty_points(order_id, type) so concurrent calls resolve to
     * the same row. Returns the credit on first award, null if the
     * order has already been credited or was refunded or cancelled before
     * the award ran (the stored status is read, since a queued award can
     * run after the order object was loaded).
     */
    public function creditOrder(Order $order): ?LoyaltyPoint
    {
        if (! $this->settings->loyalty->enabled) {
            return null;
        }

        if (! $order->customer_id) {
            return null;
        }

        if (! $this->stillEarnsPoints($order)) {
            return null;
        }

        $points = $this->calculatePoints($order);

        if ($points <= 0) {
            return null;
        }

        $credit = LoyaltyPoint::query()->firstOrCreate(
            [
                'order_id' => $order->id,
                'type' => LoyaltyPointType::Earned,
            ],
            [
                'customer_id' => $order->customer_id,
                'points' => $points,
                'description' => "Earned from order #{$order->id}",
            ],
        );

        return $credit->wasRecentlyCreated ? $credit : null;
    }

    /**
     * Take back the points a fully refunded order earned.
     *
     * Writes one offsetting row for the points the order's credit holds,
     * unique per order and type like the credit itself, so a repeated refund
     * is a no-op. Not gated on the loyalty setting: points that were earned
     * come back even if the program has been switched off since. The balance
     * may go negative when the points were already redeemed. Returns the
     * reversal on first write, null when the order earned nothing or was
     * already reversed.
     */
    public function reverseOrder(Order $order): ?LoyaltyPoint
    {
        $credit = LoyaltyPoint::query()->earned()->forOrder($order)->first();

        if (! $credit instanceof LoyaltyPoint) {
            return null;
        }

        $reversal = LoyaltyPoint::query()->firstOrCreate(
            [
                'order_id' => $order->id,
                'type' => LoyaltyPointType::Reversed,
            ],
            [
                'customer_id' => $credit->customer_id,
                'points' => $credit->points,
                'description' => "Reversed: order #{$order->id} was refunded",
            ],
        );

        return $reversal->wasRecentlyCreated ? $reversal : null;
    }

    private function stillEarnsPoints(Order $order): bool
    {
        $paymentStatus = Order::query()->whereKey($order->id)->value('payment_status');

        return ! $paymentStatus instanceof PaymentStatus || $paymentStatus->earnsLoyaltyPoints();
    }

    private function calculatePoints(Order $order): int
    {
        $base = $order->total->dollars() * $this->settings->loyalty->pointsPerDollar;

        $multiplier = $order->customer
            ? $this->customerLoyalty->pointsMultiplier($order->customer)
            : 1.0;

        return (int) floor($base * $multiplier);
    }
}
