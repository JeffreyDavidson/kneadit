<?php

namespace App\Actions\Customers;

use App\Enums\Financial\CouponType;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Support\Facades\Date;

class CreateBirthdayCoupon
{
    public function __invoke(Customer $customer, int $discountPercent, int $validDays = 7): ?Coupon
    {
        if ($discountPercent <= 0) {
            return null;
        }

        $today = Date::today();
        $couponCode = "BDAY-{$customer->id}-{$today->year}";

        return Coupon::query()->firstOrCreate(['code' => $couponCode], [
            'type' => CouponType::Percentage,
            'percentage' => $discountPercent,
            'max_uses' => 1,
            'used_count' => 0,
            'starts_at' => $today,
            'expires_at' => $today->copy()->addDays($validDays),
            'is_active' => true,
        ]);
    }
}
