<?php

namespace App\Services\Loyalty;

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\ValueObjects\LoyaltyBalance;
use Illuminate\Database\Eloquent\Collection;

class CustomerLoyalty
{
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
}
