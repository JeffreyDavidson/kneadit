<?php

declare(strict_types=1);

namespace App\Services\Loyalty;

use App\Enums\Engagement\LoyaltyTier;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\LoyaltyBalance;
use Illuminate\Database\Eloquent\Collection;

class CustomerLoyalty
{
    public function __construct(
        private readonly TenantSettings $settings,
    ) {}

    /**
     * Get the full loyalty snapshot for a customer: balance and recent history.
     *
     * @return array{balance: LoyaltyBalance, history: Collection<int, LoyaltyPoint>}
     */
    public function snapshot(Customer $customer, int $historyLimit = 20): array
    {
        return [
            'balance' => $this->balance($customer),
            'history' => $customer->loyaltyPoints()
                ->latest('created_at')
                ->limit($historyLimit)
                ->get(),
        ];
    }

    public function balance(Customer $customer): LoyaltyBalance
    {
        return $customer->loyaltyPoints()->balance();
    }

    /**
     * Resolve the customer's current tier from lifetime earned points, less
     * any that a refund took back. Tiers are based on what was earned, not
     * the redeemable balance — spending rewards shouldn't drop a customer
     * down a tier, but a refunded order no longer counts.
     */
    public function tier(Customer $customer): LoyaltyTier
    {
        $earned = $this->balance($customer)->netEarned;
        $loyalty = $this->settings->loyalty;

        return match (true) {
            $earned >= $loyalty->tierPlatinumThreshold => LoyaltyTier::Platinum,
            $earned >= $loyalty->tierGoldThreshold => LoyaltyTier::Gold,
            $earned >= $loyalty->tierSilverThreshold => LoyaltyTier::Silver,
            default => LoyaltyTier::Bronze,
        };
    }

    /**
     * @return array{next: ?LoyaltyTier, pointsToNext: int}
     */
    public function nextTierProgress(Customer $customer): array
    {
        $current = $this->tier($customer);
        $next = $current->next();

        if (! $next instanceof LoyaltyTier) {
            return ['next' => null, 'pointsToNext' => 0];
        }

        $earned = $this->balance($customer)->netEarned;
        $loyalty = $this->settings->loyalty;

        $threshold = match ($next) {
            LoyaltyTier::Silver => $loyalty->tierSilverThreshold,
            LoyaltyTier::Gold => $loyalty->tierGoldThreshold,
            LoyaltyTier::Platinum => $loyalty->tierPlatinumThreshold,
            default => 0,
        };

        return [
            'next' => $next,
            'pointsToNext' => max(0, $threshold - $earned),
        ];
    }

    /**
     * Points multiplier the customer's tier qualifies for.
     * Returns 1.0 when tier perks are disabled or the customer is Bronze.
     */
    public function pointsMultiplier(Customer $customer): float
    {
        $loyalty = $this->settings->loyalty;
        if (! $loyalty->tierPerksEnabled) {
            return 1.0;
        }

        return match ($this->tier($customer)) {
            LoyaltyTier::Silver => $loyalty->tierSilverMultiplier,
            LoyaltyTier::Gold => $loyalty->tierGoldMultiplier,
            LoyaltyTier::Platinum => $loyalty->tierPlatinumMultiplier,
            LoyaltyTier::Bronze => 1.0,
        };
    }

    /**
     * Whether the customer's current tier qualifies for free delivery.
     */
    public function qualifiesForFreeDelivery(Customer $customer): bool
    {
        $loyalty = $this->settings->loyalty;
        if (! $loyalty->tierPerksEnabled) {
            return false;
        }

        return match ($this->tier($customer)) {
            LoyaltyTier::Silver => $loyalty->tierSilverFreeDelivery,
            LoyaltyTier::Gold => $loyalty->tierGoldFreeDelivery,
            LoyaltyTier::Platinum => $loyalty->tierPlatinumFreeDelivery,
            LoyaltyTier::Bronze => false,
        };
    }
}
