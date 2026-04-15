<?php

namespace App\Actions\Loyalty;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;

class AdjustLoyaltyPoints
{
    public function __invoke(Customer $customer, int $points, string $description): LoyaltyPoint
    {
        return LoyaltyPoint::query()->create([
            'customer_id' => $customer->id,
            'points' => $points,
            'type' => LoyaltyPointType::Adjusted,
            'description' => $description,
        ]);
    }
}
