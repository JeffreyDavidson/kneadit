<?php

use App\Actions\Loyalty\AdjustLoyaltyPoints;
use App\Actions\Loyalty\RedeemLoyaltyPoints;
use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Loyalty\LoyaltyLedger;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);
});

test('credits points for a delivered order', function () {
    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->delivered()
        ->create(['total' => 25.50, 'subtotal' => 25.50]);

    $point = resolve(LoyaltyLedger::class)->creditOrder($order);

    expect($point)
        ->not->toBeNull()
        ->points->toBe(255)
        ->type->toBe(LoyaltyPointType::Earned)
        ->customer_id->toBe(testFixture('customer', Customer::class)->id)
        ->order_id->toBe($order->id);
});

test('returns null when loyalty is disabled', function () {
    settings(['loyalty_enabled' => '0']);

    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->delivered()
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    $result = resolve(LoyaltyLedger::class)->creditOrder($order);

    expect($result)->toBeNull()
        ->and(LoyaltyPoint::query()->count())->toBe(0);
});

test('returns null when order has no customer', function () {
    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->delivered()
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    // Simulate an order without a customer by clearing the field in memory
    $order->customer_id = null;

    $result = resolve(LoyaltyLedger::class)->creditOrder($order);

    expect($result)->toBeNull();
});

test('is idempotent for the same order', function () {
    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->delivered()
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    $ledger = resolve(LoyaltyLedger::class);
    $first = $ledger->creditOrder($order);
    $second = $ledger->creditOrder($order);

    expect($first)->not->toBeNull()
        ->and($second)->toBeNull()
        ->and(LoyaltyPoint::earned()->forOrder($order)->count())->toBe(1);
});

test('returns null when calculated points are zero', function () {
    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->delivered()
        ->create(['total' => 0.00, 'subtotal' => 0.00]);

    $result = resolve(LoyaltyLedger::class)->creditOrder($order);

    expect($result)->toBeNull()
        ->and(LoyaltyPoint::query()->count())->toBe(0);
});

test('redeems points for a customer', function () {
    $point = resolve(RedeemLoyaltyPoints::class)(testFixture('customer', Customer::class), 50, 'Free cookie reward');

    expect($point)
        ->points->toBe(50)
        ->type->toBe(LoyaltyPointType::Redeemed)
        ->customer_id->toBe(testFixture('customer', Customer::class)->id)
        ->description->toBe('Free cookie reward');
});

test('adjusts points for a customer', function () {
    $point = resolve(AdjustLoyaltyPoints::class)(testFixture('customer', Customer::class), 25, 'Admin correction');

    expect($point)
        ->points->toBe(25)
        ->type->toBe(LoyaltyPointType::Adjusted)
        ->customer_id->toBe(testFixture('customer', Customer::class)->id)
        ->description->toBe('Admin correction');
});
