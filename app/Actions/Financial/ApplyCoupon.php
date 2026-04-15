<?php

namespace App\Actions\Financial;

use App\Models\Financial\Coupon;

class ApplyCoupon
{
    public function __invoke(Coupon $coupon): void
    {
        Coupon::query()->where('id', $coupon->id)->increment('used_count');
    }
}
