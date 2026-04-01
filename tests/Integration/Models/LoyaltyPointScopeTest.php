<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();

    $this->customer = Customer::factory()->create();
    $this->order = Order::factory()->recycle($this->customer)->create();

    LoyaltyPoint::factory()->earned(100)->for($this->customer)->create(['order_id' => $this->order->id]);
    LoyaltyPoint::factory()->redeemed(50)->for($this->customer)->create();
    LoyaltyPoint::factory()->adjusted(25)->for($this->customer)->create();
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
