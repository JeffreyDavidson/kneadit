<?php

use App\Actions\Financial\ApplyCoupon;
use App\Models\Financial\Coupon;

beforeEach(fn () => setUpTenantTest());

test('increments coupon used count', function () {
    $coupon = Coupon::factory()->create();

    app(ApplyCoupon::class)($coupon);

    expect($coupon->refresh()->used_count)->toBe(1);
});

test('increments used count multiple times', function () {
    $coupon = Coupon::factory()->create(['used_count' => 3]);

    app(ApplyCoupon::class)($coupon);

    expect($coupon->refresh()->used_count)->toBe(4);
});
