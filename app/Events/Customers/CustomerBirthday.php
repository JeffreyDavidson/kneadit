<?php

namespace App\Events\Customers;

use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerBirthday implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly Coupon $coupon,
    ) {}
}
