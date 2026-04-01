<?php

namespace App\Events\Customers;

use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class BirthdayDiscountGenerated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly Coupon $coupon,
    ) {}
}
