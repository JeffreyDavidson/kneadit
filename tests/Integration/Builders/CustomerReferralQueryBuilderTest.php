<?php

use App\Enums\Customers\CustomerReferralStatus;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('pending returns only pending referrals', function () {
    $pending = CustomerReferral::factory()->create();
    CustomerReferral::factory()->completed()->create();
    CustomerReferral::factory()->create(['status' => CustomerReferralStatus::Cancelled]);

    $results = CustomerReferral::query()->pending()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($pending))->toBeTrue();
});

test('notCancelled excludes cancelled referrals', function () {
    CustomerReferral::factory()->create();
    CustomerReferral::factory()->completed()->create();
    CustomerReferral::factory()->create(['status' => CustomerReferralStatus::Cancelled]);

    expect(CustomerReferral::query()->notCancelled()->count())->toBe(2);
});

test('forOrder returns the referral recorded for that order', function () {
    $order = Order::factory()->create();
    $referral = CustomerReferral::factory()->create(['order_id' => $order->id]);
    CustomerReferral::factory()->create(['order_id' => Order::factory()->create()->id]);

    $results = CustomerReferral::query()->forOrder($order)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($referral))->toBeTrue();
});

test('forReferredCustomer returns referrals for that customer', function () {
    $customer = Customer::factory()->create();
    $referral = CustomerReferral::factory()->create(['referred_customer_id' => $customer->id]);
    CustomerReferral::factory()->create();

    $results = CustomerReferral::query()->forReferredCustomer($customer->id)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($referral))->toBeTrue();
});
