<?php

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('validates a valid coupon', function () {
    $coupon = Coupon::factory()->create(['code' => 'SAVE10', 'is_active' => true]);

    $service = new CouponService;
    $result = $service->validate('save10', 50.00);

    expect($result->valid)->toBeTrue()
        ->and($result->coupon->id)->toBe($coupon->id)
        ->and($result->error)->toBeNull();
});

test('returns error for nonexistent coupon', function () {
    $service = new CouponService;
    $result = $service->validate('FAKE123', 50.00);

    expect($result->valid)->toBeFalse()
        ->and($result->error)->toBe('Coupon not found.');
});

test('apply increments used count', function () {
    $coupon = Coupon::factory()->create(['used_count' => 0]);

    $service = new CouponService;
    $service->apply($coupon);

    expect($coupon->fresh()->used_count)->toBe(1);
});
