<?php

namespace App\Observers;

use App\Models\Coupon;
use Illuminate\Support\Str;

class CouponObserver
{
    public function creating(Coupon $coupon): void
    {
        $coupon->code = Str::upper($coupon->code);
    }

    public function updating(Coupon $coupon): void
    {
        $coupon->code = Str::upper($coupon->code);
    }
}
