<?php

use App\Models\Financial\Coupon;
use App\Services\Coupon\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isValid returns true for active unexpired coupon', function () {
    $coupon = Coupon::factory()->create();

    expect(resolve(CouponService::class)->isValid($coupon))->toBeTrue();
});

test('isValid returns false for expired coupon', function () {
    $coupon = Coupon::factory()->expired()->create();

    expect(resolve(CouponService::class)->isValid($coupon))->toBeFalse();
});

test('isValid returns false when max uses reached', function () {
    $coupon = Coupon::factory()->maxedOut(5)->create();

    expect(resolve(CouponService::class)->isValid($coupon))->toBeFalse();
});

test('percentage discount calculates correctly', function () {
    $coupon = Coupon::factory()->percentage()->create(['percentage' => 20]);

    expect(resolve(CouponService::class)->calculateDiscount($coupon, 100.00))->toBe(20.00)
        ->and(resolve(CouponService::class)->calculateDiscount($coupon, 50.00))->toBe(10.00);
});

test('fixed discount does not exceed subtotal', function () {
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 25]);

    expect(resolve(CouponService::class)->calculateDiscount($coupon, 100.00))->toBe(25.00)
        ->and(resolve(CouponService::class)->calculateDiscount($coupon, 15.00))->toBe(15.00);
});

test('isValid returns false for future start date coupon', function () {
    $coupon = Coupon::factory()->create([
        'starts_at' => now()->addWeek(),
        'expires_at' => now()->addMonth(),
    ]);

    expect(resolve(CouponService::class)->isValid($coupon))->toBeFalse();
});

test('discount returns zero when below minimum order amount', function () {
    $coupon = Coupon::factory()->percentage()->create([
        'percentage' => 20,
        'min_order_amount' => 50,
    ]);

    expect(resolve(CouponService::class)->calculateDiscount($coupon, 30.00))->toBe(0.0)
        ->and(resolve(CouponService::class)->calculateDiscount($coupon, 60.00))->toBe(12.00);
});
