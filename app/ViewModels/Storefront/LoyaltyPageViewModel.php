<?php

namespace App\ViewModels\Storefront;

use App\Enums\Engagement\LoyaltyPointType;
use App\Enums\Engagement\LoyaltyTier;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyReward;
use App\Services\Loyalty\CustomerLoyalty;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\LoyaltyBalance;
use Illuminate\Support\Collection;

class LoyaltyPageViewModel
{
    /**
     * @param Collection<int, LoyaltyReward> $rewards
     */
    public static function forCustomer(TenantSettings $settings, Customer $customer, CustomerLoyalty $customerLoyalty, Collection $rewards): self
    {
        $snapshot = $customerLoyalty->snapshot($customer);
        [$content, $howSteps] = self::loadContent();

        $tiersEnabled = $settings->loyalty->tiersEnabled;
        $progress = $tiersEnabled ? $customerLoyalty->nextTierProgress($customer) : null;
        $perksEnabled = $settings->loyalty->tierPerksEnabled;

        return new self(
            settings: $settings,
            customer: $customer,
            balance: $snapshot['balance'],
            history: $snapshot['history'],
            rewards: $rewards,
            content: $content,
            howSteps: $howSteps,
            tier: $tiersEnabled ? $customerLoyalty->tier($customer) : null,
            nextTier: $progress['next'] ?? null,
            pointsToNextTier: $progress['pointsToNext'] ?? 0,
            tierMultiplier: $perksEnabled ? $customerLoyalty->pointsMultiplier($customer) : 1.0,
            tierFreeDelivery: $perksEnabled && $customerLoyalty->qualifiesForFreeDelivery($customer),
        );
    }

    /**
     * @param Collection<int, LoyaltyReward> $rewards
     */
    public static function notFound(TenantSettings $settings, Collection $rewards): self
    {
        [$content, $howSteps] = self::loadContent();

        return new self(
            settings: $settings,
            customer: null,
            balance: new LoyaltyBalance(earned: 0, redeemed: 0, adjusted: 0),
            history: collect(),
            rewards: $rewards,
            content: $content,
            howSteps: $howSteps,
            customerNotFound: true,
        );
    }

    /**
     * @param Collection<int, LoyaltyReward> $rewards
     */
    public static function empty(TenantSettings $settings, Collection $rewards): self
    {
        [$content, $howSteps] = self::loadContent();

        return new self(
            settings: $settings,
            customer: null,
            balance: new LoyaltyBalance(earned: 0, redeemed: 0, adjusted: 0),
            history: collect(),
            rewards: $rewards,
            content: $content,
            howSteps: $howSteps,
        );
    }

    /**
     * @return array{array<string, string>, array<int, array<string, string>>}
     */
    private static function loadContent(): array
    {
        $content = settingsPageContent('loyalty');

        return [$content, $content['how_it_works_steps'] ?? config('kneadit.default_loyalty_steps')];
    }

    public readonly int $totalPoints;

    public readonly int $lifetimeEarned;

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
        public readonly array $content = [],
        public readonly array $howSteps = [],
        public readonly bool $customerNotFound = false,
        public readonly ?LoyaltyTier $tier = null,
        public readonly ?LoyaltyTier $nextTier = null,
        public readonly int $pointsToNextTier = 0,
        public readonly float $tierMultiplier = 1.0,
        public readonly bool $tierFreeDelivery = false,
    ) {
        $this->totalPoints = $balance->total;
        $this->lifetimeEarned = $balance->earned;
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

    public function pointsToNextReward(): int
    {
        if (! $this->nextReward) {
            return 0;
        }

        return $this->nextReward->points_required - $this->totalPoints;
    }

    public function canRedeem(LoyaltyReward $reward): bool
    {
        return $this->hasCustomer && $this->totalPoints >= $reward->points_required;
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
}
