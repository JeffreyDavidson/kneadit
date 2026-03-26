<?php

namespace App\Actions\Orders;

use App\Enums\LoyaltyPointType;
use App\Models\LoyaltyPoint;
use App\Models\Order;

class AwardLoyaltyPoints
{
    public function __invoke(Order $order): void
    {
        if (settings('loyalty_enabled') !== '1') {
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
        $pointsPerDollar = (int) settings('loyalty_points_per_dollar', '10');

        return (int) floor((float) $order->total * $pointsPerDollar);
    }
}
