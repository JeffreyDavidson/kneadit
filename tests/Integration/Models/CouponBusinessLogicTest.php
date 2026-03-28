<?php

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isValid returns true for active unexpired coupon', function () {
    $coupon = Coupon::factory()->create();

    expect($coupon->isValid())->toBeTrue();
});

test('isValid returns false for expired coupon', function () {
    $coupon = Coupon::factory()->expired()->create();

    expect($coupon->isValid())->toBeFalse();
});

test('isValid returns false when max uses reached', function () {
    $coupon = Coupon::factory()->create(['max_uses' => 5, 'used_count' => 5]);

    expect($coupon->isValid())->toBeFalse();
});

test('percentage discount calculates correctly', function () {
    $coupon = Coupon::factory()->percentage()->create(['value' => 20]);

    expect($coupon->calculateDiscount(100.00))->toBe(20.00)
        ->and($coupon->calculateDiscount(50.00))->toBe(10.00);
});

test('fixed discount does not exceed subtotal', function () {
    $coupon = Coupon::factory()->fixed()->create(['value' => 25]);

    expect($coupon->calculateDiscount(100.00))->toBe(25.00)
        ->and($coupon->calculateDiscount(15.00))->toBe(15.00);
});

test('isValid returns false for future start date coupon', function () {
    $coupon = Coupon::factory()->create([
        'starts_at' => now()->addWeek(),
        'expires_at' => now()->addMonth(),
    ]);

    expect($coupon->isValid())->toBeFalse();
});

test('discount returns zero when below minimum order amount', function () {
    $coupon = Coupon::factory()->percentage()->create([
        'value' => 20,
        'min_order_amount' => 50,
    ]);

    expect($coupon->calculateDiscount(30.00))->toBe(0.0)
        ->and($coupon->calculateDiscount(60.00))->toBe(12.00);
});
