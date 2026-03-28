<?php

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('order status is cast to enum', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    expect($order->fresh()->status)->toBeInstanceOf(OrderStatus::class)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('order payment status is cast to enum', function () {
    $order = Order::factory()->paid()->create();

    expect($order->fresh()->payment_status)->toBeInstanceOf(PaymentStatus::class)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('user role is cast to enum', function () {
    $user = User::factory()->owner()->create();

    expect($user->fresh()->role)->toBeInstanceOf(UserRole::class)
        ->and($user->fresh()->role)->toBe(UserRole::Owner);
});

test('coupon type is cast to enum', function () {
    $coupon = Coupon::factory()->percentage()->create();

    expect($coupon->fresh()->type)->toBeInstanceOf(CouponType::class)
        ->and($coupon->fresh()->type)->toBe(CouponType::Percentage);
});
