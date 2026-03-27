<?php

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;

beforeEach(function () {
    setUpTenantTest();
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

it('uppercases coupon code on creation via observer', function () {
    $coupon = Coupon::query()->create([
        'code' => 'summer25',
        'type' => CouponType::Percentage,
        'value' => 25,
        'is_active' => true,
    ]);

    expect($coupon->code)->toBe('SUMMER25');
});
