<?php

use App\Actions\Orders\AwardLoyaltyPoints;
use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('awards loyalty points based on order total', function () {
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['total' => 25.00]);

    app(AwardLoyaltyPoints::class)($order);

    $point = LoyaltyPoint::query()->first();

    expect($point)->not->toBeNull()
        ->and($point->customer_id)->toBe($customer->id)
        ->and($point->points)->toBe(250)
        ->and($point->type)->toBe(LoyaltyPointType::Earned)
        ->and($point->order_id)->toBe($order->id);
});

test('does not award points when loyalty is disabled', function () {
    settings(['loyalty_enabled' => '0']);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['total' => 50.00]);

    app(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->count())->toBe(0);
});

test('does not award duplicate points for same order', function () {
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['total' => 10.00]);

    app(AwardLoyaltyPoints::class)($order);
    app(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->count())->toBe(1);
});

test('does not award points when calculated points are zero', function () {
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '0']);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['total' => 50.00]);

    app(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->count())->toBe(0);
});

test('floors fractional points', function () {
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '3']);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create(['total' => 10.33]);

    app(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->first()->points)->toBe(30);
});
