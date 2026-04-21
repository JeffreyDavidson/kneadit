<?php

namespace App\Builders\Engagement;

use App\Models\Engagement\LoyaltyReward;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<LoyaltyReward> */
class LoyaltyRewardQueryBuilder extends Builder
{
    public function active(): static
    {
        $this->where('is_active', true);

        return $this;
    }

    public function forStorefront(): static
    {
        $this->active()->orderBy('points_required');

        return $this;
    }
}
