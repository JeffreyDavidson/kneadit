<?php

use App\Enums\Financial\CouponType;
use App\Models\Financial\Coupon;
use App\Models\Staff\User;

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

it('uppercases coupon code on creation via observer', function () {
    $coupon = Coupon::factory()->create([
        'code' => 'summer25',
        'type' => CouponType::Percentage,
        'percentage' => 25,
        'is_active' => true,
    ]);

    expect($coupon->code)->toBe('SUMMER25');
});
