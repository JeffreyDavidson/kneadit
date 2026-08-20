<?php

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderDelivered;
use App\Listeners\Orders\AwardLoyaltyPointsListener;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create(['email' => 'buyer@example.com']);
});

test('credits loyalty points for a delivered order', function () {
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);

    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->delivered()
        ->create(['total' => 20.00, 'subtotal' => 20.00]);

    resolve(AwardLoyaltyPointsListener::class)->handle(
        new OrderDelivered($order, OrderStatus::Ready),
    );

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(1);
});

test('does not credit when loyalty is disabled', function () {
    settings(['loyalty_enabled' => '0']);

    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->delivered()
        ->create(['total' => 20.00, 'subtotal' => 20.00]);

    resolve(AwardLoyaltyPointsListener::class)->handle(
        new OrderDelivered($order, OrderStatus::Ready),
    );

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});
