<?php

use App\Actions\Customers\CreateBirthdayCoupon;
use App\Enums\Financial\CouponType;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;

beforeEach(fn () => setUpTenantTest());

test('creates birthday coupon for customer', function () {
    $customer = Customer::factory()->create();

    $coupon = app(CreateBirthdayCoupon::class)($customer, 15);

    expect($coupon)
        ->toBeInstanceOf(Coupon::class)
        ->code->toBe("BDAY-{$customer->id}-" . now()->year)
        ->type->toBe(CouponType::Percentage)
        ->value->toBe('15.00')
        ->max_uses->toBe(1)
        ->used_count->toBe(0)
        ->is_active->toBeTrue();
});

test('returns null when discount percent is zero', function () {
    $customer = Customer::factory()->create();

    $result = app(CreateBirthdayCoupon::class)($customer, 0);

    expect($result)->toBeNull();
});

test('returns null when discount percent is negative', function () {
    $customer = Customer::factory()->create();

    $result = app(CreateBirthdayCoupon::class)($customer, -10);

    expect($result)->toBeNull();
});

test('returns existing coupon on retry (idempotent)', function () {
    $customer = Customer::factory()->create();

    $first = app(CreateBirthdayCoupon::class)($customer, 15);
    $second = app(CreateBirthdayCoupon::class)($customer, 20);

    expect($first->id)->toBe($second->id);
    expect(Coupon::query()->count())->toBe(1);
});

test('uses custom valid days', function () {
    $customer = Customer::factory()->create();

    $coupon = app(CreateBirthdayCoupon::class)($customer, 10, 14);

    expect($coupon->expires_at->toDateString())
        ->toBe(now()->addDays(14)->toDateString());
});
