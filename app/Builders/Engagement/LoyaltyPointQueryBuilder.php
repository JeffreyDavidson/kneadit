<?php

namespace App\Builders\Engagement;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of LoyaltyPoint
 *
 * @extends Builder<TModel>
 */
class LoyaltyPointQueryBuilder extends Builder
{
    public function earned(): static
    {
        $this->where('type', LoyaltyPointType::Earned);

        return $this;
    }

    public function redeemed(): static
    {
        $this->where('type', LoyaltyPointType::Redeemed);

        return $this;
    }

    public function adjusted(): static
    {
        $this->where('type', LoyaltyPointType::Adjusted);

        return $this;
    }

    public function forOrder(Order $order): static
    {
        $this->where('order_id', $order->id);

        return $this;
    }
}
