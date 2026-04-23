<?php

namespace App\Events\Customers;

use App\Models\Customers\CustomerReferral;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerReferralCompleted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly CustomerReferral $referral,
    ) {}
}
