<?php

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();

    $this->customer = Customer::factory()->create();
    $this->order = Order::factory()->create();

    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 100, 'type' => 'earned', 'description' => 'test', 'order_id' => $this->order->id]);
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 50, 'type' => 'redeemed', 'description' => 'test']);
    LoyaltyPoint::create(['customer_id' => $this->customer->id, 'points' => 25, 'type' => 'adjusted', 'description' => 'test']);
});

test('earned scope filters to earned type', function () {
    expect(LoyaltyPoint::earned()->count())->toBe(1);
});

test('redeemed scope filters to redeemed type', function () {
    expect(LoyaltyPoint::redeemed()->count())->toBe(1);
});

test('forOrder scope filters to specific order', function () {
    expect(LoyaltyPoint::forOrder($this->order)->count())->toBe(1);
});
