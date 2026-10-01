<?php

declare(strict_types=1);

namespace App\Builders\Engagement;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\ValueObjects\LoyaltyBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

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

    /**
     * Sum the matching rows into a balance: earned plus adjusted minus redeemed.
     */
    public function balance(): LoyaltyBalance
    {
        $stats = $this->selectTotals()->first();

        return new LoyaltyBalance(
            earned: Arr::integer(['value' => $stats->earned ?? 0], 'value', 0),
            redeemed: Arr::integer(['value' => $stats->redeemed ?? 0], 'value', 0),
            adjusted: Arr::integer(['value' => $stats->adjusted ?? 0], 'value', 0),
        );
    }

    /**
     * One row per customer with their earned and adjusted points and their balance (earned plus adjusted minus redeemed).
     */
    public function balancesByCustomer(): static
    {
        $this->select('customer_id')
            ->selectTotals()
            ->selectRaw(
                'coalesce(sum(case when type = ? then points else 0 end), 0)'
                .' + coalesce(sum(case when type = ? then points else 0 end), 0)'
                .' - coalesce(sum(case when type = ? then points else 0 end), 0) as balance',
                [LoyaltyPointType::Earned->value, LoyaltyPointType::Adjusted->value, LoyaltyPointType::Redeemed->value],
            )
            ->groupBy('customer_id');

        return $this;
    }

    private function selectTotals(): static
    {
        $this
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as earned', [LoyaltyPointType::Earned->value])
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as adjusted', [LoyaltyPointType::Adjusted->value])
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as redeemed', [LoyaltyPointType::Redeemed->value]);

        return $this;
    }
}
