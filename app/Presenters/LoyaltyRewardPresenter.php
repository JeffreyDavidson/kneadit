<?php

namespace App\Presenters;

use App\Enums\Engagement\RewardType;
use App\Models\Engagement\LoyaltyReward;

final class LoyaltyRewardPresenter
{
    public function __construct(
        public readonly LoyaltyReward $reward,
    ) {}

    public static function for(LoyaltyReward $reward): self
    {
        return new self($reward);
    }

    public function rewardTypeLabel(): string
    {
        return match ($this->reward->reward_type) {
            RewardType::PercentageDiscount => ($this->reward->discount_percentage?->formatted(2) ?? '0.00%') . ' Off',
            RewardType::FixedDiscount => ($this->reward->discount_amount?->formatted() ?? '$0.00') . ' Off',
            RewardType::FreeProduct => 'Free ' . ($this->reward->product->name ?? 'Product'),
        };
    }
}
