<?php

namespace App\Actions\Customers;

use App\Enums\Financial\CouponType;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;

class CreateBirthdayCoupon
{
    public function __invoke(Customer $customer, int $discountPercentage, int $validDays = 7): ?Coupon
    {
        $code = 'BDAY-' . strtoupper(bin2hex(random_bytes(3)));

        return Coupon::query()->create([
            'code' => $code,
            'type' => CouponType::Percentage,
            'value' => $discountPercentage,
            'max_uses' => 1,
            'starts_at' => now(),
            'expires_at' => now()->addDays($validDays),
            'is_active' => true,
        ]);
    }
}
