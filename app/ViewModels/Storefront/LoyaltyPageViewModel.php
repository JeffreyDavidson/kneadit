<?php

namespace App\ViewModels\Storefront;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\LoyaltyBalance;
use Illuminate\Support\Collection;

class LoyaltyPageViewModel
{
    public readonly int $totalPoints;

    public readonly int $lifetimeEarned;

    public readonly string $formattedTotalPoints;

    public readonly string $formattedLifetimeEarned;

    public readonly ?LoyaltyReward $nextReward;

    public readonly bool $hasCustomer;

    /**
     * @param Collection<int, LoyaltyReward> $rewards
     * @param Collection<int, \App\Models\Engagement\LoyaltyPoint> $history
     * @param array<string, string> $content
     * @param array<int, array<string, string>> $howSteps
     */
    public function __construct(
        public readonly TenantSettings $settings,
        public readonly ?Customer $customer,
        LoyaltyBalance $balance,
        public readonly Collection $history,
        public readonly Collection $rewards,
        public readonly array $content,
        public readonly array $howSteps,
        public readonly bool $customerNotFound = false,
    ) {
        $this->totalPoints = $balance->total;
        $this->lifetimeEarned = $balance->earned;
        $this->formattedTotalPoints = number_format($this->totalPoints);
        $this->formattedLifetimeEarned = number_format($this->lifetimeEarned);
        $this->hasCustomer = $customer !== null;

        $this->nextReward = $rewards
            ->where('points_required', '>', $this->totalPoints)
            ->first();
    }

    public function nextRewardProgressPercent(): float
    {
        if (! $this->nextReward) {
            return 0;
        }

        return min(100, ($this->totalPoints / $this->nextReward->points_required) * 100);
    }

    public function formattedPointsToNextReward(): string
    {
        if (! $this->nextReward) {
            return '0';
        }

        return number_format($this->nextReward->points_required - $this->totalPoints);
    }

    public function formattedNextRewardRequired(): string
    {
        if (! $this->nextReward) {
            return '0';
        }

        return number_format($this->nextReward->points_required);
    }

    public function canRedeem(LoyaltyReward $reward): bool
    {
        return $this->hasCustomer && $this->totalPoints >= $reward->points_required;
    }

    public function formattedRewardPoints(LoyaltyReward $reward): string
    {
        return number_format($reward->points_required);
    }

    public function historyEntrySign(LoyaltyPointType $type): string
    {
        return match ($type) {
            LoyaltyPointType::Redeemed => '-',
            default => '+',
        };
    }

    public function historyEntryColorClass(LoyaltyPointType $type): string
    {
        return match ($type) {
            LoyaltyPointType::Earned => 'text-green-600',
            LoyaltyPointType::Redeemed => 'text-red-600',
            LoyaltyPointType::Adjusted => 'text-yellow-600',
        };
    }

    public function formattedEntryPoints(int $points): string
    {
        return number_format($points);
    }
}
