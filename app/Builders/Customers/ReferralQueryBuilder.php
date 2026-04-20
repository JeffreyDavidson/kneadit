<?php

namespace App\Builders\Customers;

use App\Enums\Customers\ReferralStatus;
use App\Models\Customers\Referral;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Referral> */
class ReferralQueryBuilder extends Builder
{
    public function pending(): static
    {
        $this->where('status', ReferralStatus::Pending);

        return $this;
    }

    public function rewarded(): static
    {
        $this->where('status', ReferralStatus::Rewarded);

        return $this;
    }

    /** Completed or Rewarded — both represent successful referrals. */
    public function successful(): static
    {
        $this->whereIn('status', [ReferralStatus::Completed, ReferralStatus::Rewarded]);

        return $this;
    }
}
