<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Customers\CustomerIntelligence;
use App\Services\Loyalty\LoyaltyLedger;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    test()->user = User::factory()->owner()->create();
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);
    test()->customer = Customer::factory()->create();
});

test('points awarded when order delivered', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    assertDatabaseHas('loyalty_points', [
        'customer_id' => test()->customer->id,
        'order_id' => $order->id,
        'type' => 'earned',
    ]);
});

test('points calculated correctly', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['total' => 25.50, 'subtotal' => 25.50]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    $points = LoyaltyPoint::query()->where('order_id', $order->id)->first();

    expect($points->points)->toBe(255); // 25.50 * 10
});

test('points not awarded when loyalty disabled', function () {
    settings(['loyalty_enabled' => '0']);

    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});

test('points not double awarded', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Baking);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Ready);
    resolve(TransitionOrderStatus::class)($order->fresh(), OrderStatus::Delivered);

    // Manually award again to test idempotency
    resolve(LoyaltyLedger::class)->creditOrder($order->fresh());

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->where('type', 'earned')->count())->toBe(1);
});

test('total points calculated correctly', function () {
    LoyaltyPoint::factory()->earned(100)->for(test()->customer)->create();
    LoyaltyPoint::factory()->earned(50)->for(test()->customer)->create();
    LoyaltyPoint::factory()->redeemed(30)->for(test()->customer)->create();

    expect(resolve(CustomerIntelligence::class)->metrics(test()->customer)->totalPoints)->toBe(120); // 100 + 50 - 30
});

test('lifetime points only counts earned', function () {
    LoyaltyPoint::factory()->earned(100)->for(test()->customer)->create();
    LoyaltyPoint::factory()->earned(50)->for(test()->customer)->create();
    LoyaltyPoint::factory()->redeemed(30)->for(test()->customer)->create();

    expect(resolve(CustomerIntelligence::class)->metrics(test()->customer)->lifetimePointsEarned)->toBe(150);
});
