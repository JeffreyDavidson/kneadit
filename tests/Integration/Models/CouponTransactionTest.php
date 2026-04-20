<?php

use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
});

test('coupon transaction belongs to coupon', function () {
    $coupon = Coupon::factory()->create();
    $transaction = CouponTransaction::factory()->recycle($coupon)->create();

    expect($transaction->coupon)->toBeInstanceOf(Coupon::class)
        ->and($transaction->coupon->id)->toBe($coupon->id);
});

test('coupon transaction belongs to order', function () {
    $order = Order::factory()->recycle(test()->user)->create();
    $transaction = CouponTransaction::factory()->for($order)->create();

    expect($transaction->order)->toBeInstanceOf(Order::class)
        ->and($transaction->order->id)->toBe($order->id);
});
