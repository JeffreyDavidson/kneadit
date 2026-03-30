<?php

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('uppercases coupon code on creation via observer', function () {
    $coupon = Coupon::factory()->create([
        'code' => 'summer25',
        'type' => CouponType::Percentage,
        'value' => 25,
        'is_active' => true,
    ]);

    expect($coupon->code)->toBe('SUMMER25');
});
