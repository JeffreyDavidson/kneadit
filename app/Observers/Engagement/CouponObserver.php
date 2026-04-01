<?php

namespace App\Observers\Engagement;

use App\Models\Financial\Coupon;
use Illuminate\Support\Str;

class CouponObserver
{
    public function saving(Coupon $coupon): void
    {
        $coupon->code = Str::upper($coupon->code);
    }
}
