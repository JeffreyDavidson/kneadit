<?php

use App\Actions\Orders\AwardLoyaltyPoints;
use App\Enums\LoyaltyPointType;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    $this->user = User::query()->create(['name' => 'Test', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
    $this->customer = Customer::query()->create(['name' => 'Test Customer', 'email' => 'customer@test.com']);
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);
});

test('awards points based on order total and points per dollar setting', function () {
    $order = Order::query()->create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => OrderStatus::Delivered,
        'total' => 25.50,
        'subtotal' => 25.50,
    ]);

    resolve(AwardLoyaltyPoints::class)($order);

    $points = LoyaltyPoint::query()->where('order_id', $order->id)->first();

    expect($points)
        ->not->toBeNull()
        ->points->toBe(255)
        ->type->toBe(LoyaltyPointType::Earned)
        ->customer_id->toBe($this->customer->id);
});

test('skips when loyalty is disabled', function () {
    settings(['loyalty_enabled' => '0']);

    $order = Order::query()->create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => OrderStatus::Delivered,
        'total' => 25.00,
        'subtotal' => 25.00,
    ]);

    resolve(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});

test('does not double award points', function () {
    $order = Order::query()->create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => OrderStatus::Delivered,
        'total' => 25.00,
        'subtotal' => 25.00,
    ]);

    $action = resolve(AwardLoyaltyPoints::class);
    $action($order);
    $action($order);

    expect(LoyaltyPoint::earned()->forOrder($order)->count())->toBe(1);
});

test('skips when calculated points are zero', function () {
    $order = Order::query()->create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => OrderStatus::Delivered,
        'total' => 0.00,
        'subtotal' => 0.00,
    ]);

    resolve(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});
