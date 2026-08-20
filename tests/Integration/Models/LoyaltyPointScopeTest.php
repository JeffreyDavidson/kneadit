<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();

    test()->customer = Customer::factory()->create();
    test()->order = Order::factory()->recycle(testFixture('customer', Customer::class))->create();

    LoyaltyPoint::factory()->earned(100)->for(testFixture('customer', Customer::class))->create(['order_id' => testFixture('order', Order::class)->id]);
    LoyaltyPoint::factory()->redeemed(50)->for(testFixture('customer', Customer::class))->create();
    LoyaltyPoint::factory()->adjusted(25)->for(testFixture('customer', Customer::class))->create();
});

test('earned scope filters to earned type', function () {
    expect(LoyaltyPoint::earned()->count())->toBe(1);
});

test('redeemed scope filters to redeemed type', function () {
    expect(LoyaltyPoint::redeemed()->count())->toBe(1);
});

test('forOrder scope filters to specific order', function () {
    expect(LoyaltyPoint::forOrder(testFixture('order', Order::class))->count())->toBe(1);
});
