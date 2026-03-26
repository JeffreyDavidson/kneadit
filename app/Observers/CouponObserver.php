<?php

namespace App\Observers;

use App\Models\Coupon;

class CouponObserver
{
    public function creating(Coupon $coupon): void
    {
        $coupon->code = strtoupper($coupon->code);
    }

    public function updating(Coupon $coupon): void
    {
        $coupon->code = strtoupper($coupon->code);
    }
}
