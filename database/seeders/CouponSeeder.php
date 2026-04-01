<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => CouponType::Percentage,
                'value' => 10.00,
                'min_order_amount' => 25.00,
                'max_uses' => 100,
                'used_count' => 23,
                'starts_at' => Date::now()->subDays(30),
                'expires_at' => Date::now()->addDays(60),
                'is_active' => true,
            ],
            [
                'code' => 'FIRST5',
                'type' => CouponType::Fixed,
                'value' => 5.00,
                'min_order_amount' => 15.00,
                'max_uses' => 50,
                'used_count' => 17,
                'starts_at' => Date::now()->subDays(45),
                'expires_at' => Date::now()->addDays(45),
                'is_active' => true,
            ],
            [
                'code' => 'HOLIDAY20',
                'type' => CouponType::Percentage,
                'value' => 20.00,
                'min_order_amount' => 40.00,
                'max_uses' => 200,
                'used_count' => 156,
                'starts_at' => Date::now()->subDays(90),
                'expires_at' => Date::now()->subDays(15), // Expired
                'is_active' => false,
            ],
            [
                'code' => 'BAKER15',
                'type' => CouponType::Percentage,
                'value' => 15.00,
                'min_order_amount' => 30.00,
                'max_uses' => null, // Unlimited uses
                'used_count' => 42,
                'starts_at' => Date::now()->subDays(20),
                'expires_at' => Date::now()->addDays(40),
                'is_active' => true,
            ],
            [
                'code' => 'FREESHIP',
                'type' => CouponType::Fixed,
                'value' => 3.00, // Fixed amount for delivery fee
                'min_order_amount' => 20.00,
                'max_uses' => 75,
                'used_count' => 8,
                'starts_at' => Date::now()->subDays(10),
                'expires_at' => Date::now()->addDays(50),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $couponData) {
            Coupon::query()->updateOrCreate(['code' => $couponData['code']], $couponData);
        }
    }
}
