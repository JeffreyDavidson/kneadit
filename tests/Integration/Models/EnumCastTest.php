<?php

use App\Enums\Financial\CouponType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Enums\Staff\UserRole;
use App\Models\Financial\Coupon;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('order status is cast to enum', function () {
    $order = Order::factory()->pending()->create();

    expect($order->refresh()->status)->toBeInstanceOf(OrderStatus::class)
        ->and($order->refresh()->status)->toBe(OrderStatus::Pending);
});

test('order payment status is cast to enum', function () {
    $order = Order::factory()->paid()->create();

    expect($order->refresh()->payment_status)->toBeInstanceOf(PaymentStatus::class)
        ->and($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('user role is cast to enum', function () {
    $user = User::factory()->owner()->create();

    expect($user->refresh()->role)->toBeInstanceOf(UserRole::class)
        ->and($user->refresh()->role)->toBe(UserRole::Owner);
});

test('coupon type is cast to enum', function () {
    $coupon = Coupon::factory()->percentage()->create();

    expect($coupon->refresh()->type)->toBeInstanceOf(CouponType::class)
        ->and($coupon->refresh()->type)->toBe(CouponType::Percentage);
});
