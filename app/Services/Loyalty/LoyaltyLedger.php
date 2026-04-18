<?php

namespace App\Services\Loyalty;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;

class LoyaltyLedger
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    /**
     * Award loyalty points for a delivered order.
     *
     * Idempotent and race-safe: relies on the unique index on
     * loyalty_points(order_id, type) so concurrent calls resolve to
     * the same row. Returns the credit on first award, null if the
     * order has already been credited.
     */
    public function creditOrder(Order $order): ?LoyaltyPoint
    {
        if (! $this->settings->loyalty->enabled) {
            return null;
        }

        if (! $order->customer_id) {
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

    private function calculatePoints(Order $order): int
    {
        return (int) floor((float) $order->total * $this->settings->loyalty->pointsPerDollar);
    }
}
