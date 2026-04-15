<?php

namespace App\Presenters;

use App\Enums\Engagement\RewardType;
use App\Models\Engagement\LoyaltyReward;
use Illuminate\Support\Number;

class LoyaltyRewardPresenter
{
    public function __construct(
        public readonly LoyaltyReward $reward,
    ) {}

    public function rewardTypeLabel(): string
    {
        return match ($this->reward->reward_type) {
            RewardType::PercentageDiscount => "{$this->reward->reward_value}% Off",
            RewardType::FixedDiscount => Number::currency((float) $this->reward->reward_value) . ' Off',
            RewardType::FreeProduct => 'Free ' . ($this->reward->product->name ?? 'Product'),
        };
    }
}
