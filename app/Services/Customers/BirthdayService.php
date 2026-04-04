<?php

namespace App\Services\Customers;

use App\Enums\Financial\CouponType;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Support\Facades\Date;

class BirthdayService
{
    /**
     * Find or create a birthday coupon for a customer.
     * Returns the coupon, or null if discount is 0.
     */
    public function findOrCreateBirthdayCoupon(Customer $customer, int $discountPercent, int $validDays = 7): ?Coupon
    {
        if ($discountPercent <= 0) {
            return null;
        }

        $today = Date::today();
        $couponCode = "BDAY-{$customer->id}-{$today->year}";

        return Coupon::query()->firstOrCreate(['code' => $couponCode], [
            'type' => CouponType::Percentage,
            'value' => $discountPercent,
            'max_uses' => 1,
            'used_count' => 0,
            'starts_at' => $today,
            'expires_at' => $today->copy()->addDays($validDays),
            'is_active' => true,
        ]);
    }
}
