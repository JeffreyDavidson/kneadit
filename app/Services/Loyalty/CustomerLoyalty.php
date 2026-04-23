<?php

namespace App\Services\Loyalty;

use App\Enums\Engagement\LoyaltyPointType;
use App\Enums\Engagement\LoyaltyTier;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\LoyaltyBalance;
use Illuminate\Database\Eloquent\Collection;

class CustomerLoyalty
{
    public function __construct(
        private TenantSettings $settings,
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
        $stats = $customer->loyaltyPoints()
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as earned', [LoyaltyPointType::Earned->value])
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as adjusted', [LoyaltyPointType::Adjusted->value])
            ->selectRaw('coalesce(sum(case when type = ? then points else 0 end), 0) as redeemed', [LoyaltyPointType::Redeemed->value])
            ->first();

        return new LoyaltyBalance(
            earned: (int) ($stats->earned ?? 0),
            redeemed: (int) ($stats->redeemed ?? 0),
            adjusted: (int) ($stats->adjusted ?? 0),
        );
    }

    /**
     * Resolve the customer's current tier from lifetime earned points.
     * Tiers are based on what was earned, not the redeemable balance —
     * spending rewards shouldn't drop a customer down a tier.
     */
    public function tier(Customer $customer): LoyaltyTier
    {
        $earned = $this->balance($customer)->earned;
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

        if ($next === null) {
            return ['next' => null, 'pointsToNext' => 0];
        }

        $earned = $this->balance($customer)->earned;
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
}
