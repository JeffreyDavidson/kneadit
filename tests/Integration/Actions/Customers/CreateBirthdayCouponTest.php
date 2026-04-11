<?php

use App\Actions\Customers\CreateBirthdayCoupon;
use App\Enums\Financial\CouponType;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('creates a percentage coupon for the customer', function () {
    $customer = Customer::factory()->create();

    $coupon = app(CreateBirthdayCoupon::class)($customer, 15);

    expect($coupon)->toBeInstanceOf(Coupon::class)
        ->and($coupon->type)->toBe(CouponType::Percentage)
        ->and((int) $coupon->value)->toBe(15)
        ->and($coupon->max_uses)->toBe(1)
        ->and($coupon->is_active)->toBeTrue();
});

test('coupon code starts with BDAY prefix', function () {
    $customer = Customer::factory()->create();

    $coupon = app(CreateBirthdayCoupon::class)($customer, 10);

    expect($coupon->code)->toStartWith('BDAY-');
});

test('coupon expires after the specified number of days', function () {
    $customer = Customer::factory()->create();

    $coupon = app(CreateBirthdayCoupon::class)($customer, 10, 14);

    expect($coupon->starts_at->isToday())->toBeTrue()
        ->and((int) $coupon->starts_at->diffInDays($coupon->expires_at))->toBe(14);
});

test('coupon defaults to 7 day expiration', function () {
    $customer = Customer::factory()->create();

    $coupon = app(CreateBirthdayCoupon::class)($customer, 10);

    expect((int) $coupon->starts_at->diffInDays($coupon->expires_at))->toBe(7);
});

test('coupon is persisted to the database', function () {
    $customer = Customer::factory()->create();

    $coupon = app(CreateBirthdayCoupon::class)($customer, 20);

    $this->assertDatabaseHas('coupons', [
        'id' => $coupon->id,
        'type' => CouponType::Percentage,
        'value' => 20,
    ]);
});
