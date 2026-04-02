<?php

use App\Enums\Engagement\LoyaltyPointType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Loyalty\LoyaltyLedger;
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

    resolve(LoyaltyLedger::class)->creditOrder($order);

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

    resolve(LoyaltyLedger::class)->creditOrder($order);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});

test('does not double award points', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create(['total' => 25.00, 'subtotal' => 25.00]);

    $ledger = resolve(LoyaltyLedger::class);
    $ledger->creditOrder($order);
    $ledger->creditOrder($order);

    expect(LoyaltyPoint::earned()->forOrder($order)->count())->toBe(1);
});

test('skips when calculated points are zero', function () {
    $order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->delivered()
        ->create(['total' => 0.00, 'subtotal' => 0.00]);

    resolve(LoyaltyLedger::class)->creditOrder($order);

    expect(LoyaltyPoint::query()->where('order_id', $order->id)->count())->toBe(0);
});
