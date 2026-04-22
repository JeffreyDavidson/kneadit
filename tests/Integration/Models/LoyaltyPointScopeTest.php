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

    test()->customer = Customer::factory()->create();
    test()->order = Order::factory()->recycle(test()->customer)->create();

    LoyaltyPoint::factory()->earned(100)->for(test()->customer)->create(['order_id' => test()->order->id]);
    LoyaltyPoint::factory()->redeemed(50)->for(test()->customer)->create();
    LoyaltyPoint::factory()->adjusted(25)->for(test()->customer)->create();
});

test('earned scope filters to earned type', function () {
    expect(LoyaltyPoint::earned()->count())->toBe(1);
});

test('redeemed scope filters to redeemed type', function () {
    expect(LoyaltyPoint::redeemed()->count())->toBe(1);
});

test('forOrder scope filters to specific order', function () {
    expect(LoyaltyPoint::forOrder(test()->order)->count())->toBe(1);
});
