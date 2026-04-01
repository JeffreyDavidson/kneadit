<?php

namespace App\Actions\Orders;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;

class AwardLoyaltyPoints
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    public function __invoke(Order $order): void
    {
        if (! $this->settings->loyaltyEnabled) {
            return;
        }

        if (! $order->customer_id) {
            return;
        }

        if (LoyaltyPoint::earned()->forOrder($order)->exists()) {
            return;
        }

        $points = $this->calculatePoints($order);

        if ($points <= 0) {
            return;
        }

        LoyaltyPoint::query()->create([
            'customer_id' => $order->customer_id,
            'points' => $points,
            'type' => LoyaltyPointType::Earned,
            'description' => "Earned from order #{$order->id}",
            'order_id' => $order->id,
        ]);
    }

    private function calculatePoints(Order $order): int
    {
        $pointsPerDollar = (int) $this->settings->loyaltyPointsPerDollar;

        return (int) floor((float) $order->total * $pointsPerDollar);
    }
}
