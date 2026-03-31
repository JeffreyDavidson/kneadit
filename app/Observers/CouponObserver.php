<?php

namespace App\Observers;

use App\Models\Coupon;
use Illuminate\Support\Str;

class CouponObserver
{
    public function saving(Coupon $coupon): void
    {
        $coupon->code = Str::upper($coupon->code);
    }
}
