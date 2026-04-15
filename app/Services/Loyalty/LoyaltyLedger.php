<?php

namespace App\Services\Loyalty;

use App\Actions\Loyalty\AdjustLoyaltyPoints;
use App\Actions\Loyalty\RedeemLoyaltyPoints;
use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
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
     * Idempotent — safe to call multiple times for the same order.
     */
    public function creditOrder(Order $order): ?LoyaltyPoint
    {
        if (! $this->settings->loyalty->enabled) {
            return null;
        }

        if (! $order->customer_id) {
            return null;
        }

        if (LoyaltyPoint::earned()->forOrder($order)->exists()) {
            return null;
        }

        $points = $this->calculatePoints($order);

        if ($points <= 0) {
            return null;
        }

        return LoyaltyPoint::query()->create([
            'customer_id' => $order->customer_id,
            'points' => $points,
            'type' => LoyaltyPointType::Earned,
            'description' => "Earned from order #{$order->id}",
            'order_id' => $order->id,
        ]);
    }

    public function redeem(Customer $customer, int $points, string $description): LoyaltyPoint
    {
        return app(RedeemLoyaltyPoints::class)($customer, $points, $description);
    }

    public function adjust(Customer $customer, int $points, string $description): LoyaltyPoint
    {
        return app(AdjustLoyaltyPoints::class)($customer, $points, $description);
    }

    private function calculatePoints(Order $order): int
    {
        return (int) floor((float) $order->total * $this->settings->loyalty->pointsPerDollar);
    }
}
