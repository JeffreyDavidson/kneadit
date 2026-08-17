<?php

use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('coupon code is uppercased on create', function () {
    $coupon = Coupon::factory()->create(['code' => 'spring20']);

    expect($coupon->code)->toBe('SPRING20');
});

test('coupon code is uppercased on update', function () {
    $coupon = Coupon::factory()->create();

    $coupon->update(['code' => 'winter10']);

    expect($coupon->refresh()->code)->toBe('WINTER10');
});
