<?php

use App\Actions\Orders\AwardLoyaltyPoints;
use App\Enums\LoyaltyPointType;
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
    $this->user = User::factory()->owner()->create();
    $this->customer = Customer::factory()->create();
    settings(['loyalty_enabled' => '1']);
    settings(['loyalty_points_per_dollar' => '10']);
});

test('awards points based on order total and points per dollar setting', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create(['total' => 25.50, 'subtotal' => 25.50]);

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

    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    resolve(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});

test('does not double award points', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    $action = resolve(AwardLoyaltyPoints::class);
    $action($order);
    $action($order);

    expect(LoyaltyPoint::earned()->forOrder($order)->count())->toBe(1);
});

test('skips when calculated points are zero', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create(['total' => 0.00, 'subtotal' => 0.00]);

    resolve(AwardLoyaltyPoints::class)($order);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});
