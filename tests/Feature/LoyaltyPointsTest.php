<?php

use App\Actions\Orders\AwardLoyaltyPoints;
use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    $this->user = User::create(['name' => 'Test', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
    Setting::set('loyalty_enabled', '1');
    Setting::set('loyalty_points_per_dollar', '10');
    $this->customer = Customer::create(['name' => 'Loyalty Tester', 'email' => 'loyal@test.com']);
});

test('points awarded when order delivered', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => 'pending',
        'total' => 25.00,
        'subtotal' => 25.00,
    ]);

    app(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    $this->assertDatabaseHas('loyalty_points', [
        'customer_id' => $this->customer->id,
        'order_id' => $order->id,
        'type' => 'earned',
    ]);
});

test('points calculated correctly', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => 'pending',
        'total' => 25.50,
        'subtotal' => 25.50,
    ]);

    app(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    $points = LoyaltyPoint::where('order_id', $order->id)->first();

    expect($points->points)->toBe(255); // 25.50 * 10
});

test('points not awarded when loyalty disabled', function () {
    Setting::set('loyalty_enabled', '0');

    $order = Order::create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => 'pending',
        'total' => 25.00,
        'subtotal' => 25.00,
    ]);

    app(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    expect(LoyaltyPoint::where('order_id', $order->id)->count())->toBe(0);
});

test('points not double awarded', function () {
    $order = Order::create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => 'pending',
        'total' => 25.00,
        'subtotal' => 25.00,
    ]);

    app(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    app(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    // Manually award again to test idempotency
    app(AwardLoyaltyPoints::class)($order->fresh());

    expect(LoyaltyPoint::where('order_id', $order->id)->where('type', 'earned')->count())->toBe(1);
});

test('total points calculated correctly', function () {
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 100, 'type' => 'earned', 'description' => 'test']);
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 50, 'type' => 'earned', 'description' => 'test']);
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 30, 'type' => 'redeemed', 'description' => 'test']);

    expect($this->customer->total_points)->toBe(120); // 100 + 50 - 30
});

test('lifetime points only counts earned', function () {
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 100, 'type' => 'earned', 'description' => 'test']);
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 50, 'type' => 'earned', 'description' => 'test']);
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 30, 'type' => 'redeemed', 'description' => 'test']);

    expect($this->customer->lifetime_points_earned)->toBe(150);
});
