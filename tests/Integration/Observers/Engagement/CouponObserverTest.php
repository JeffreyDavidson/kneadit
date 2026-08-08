<?php

use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('saving uppercases the coupon code on create', function () {
    $coupon = Coupon::factory()->create(['code' => 'summer25']);

    expect($coupon->code)->toBe('SUMMER25');
});

test('saving uppercases the coupon code on update', function () {
    $coupon = Coupon::factory()->create(['code' => 'ORIGINAL']);

    $coupon->update(['code' => 'updated-code']);

    expect($coupon->fresh()->code)->toBe('UPDATED-CODE');
});

test('saving preserves already uppercased codes', function () {
    $coupon = Coupon::factory()->create(['code' => 'ALREADY-UPPER']);

    expect($coupon->code)->toBe('ALREADY-UPPER');
});
