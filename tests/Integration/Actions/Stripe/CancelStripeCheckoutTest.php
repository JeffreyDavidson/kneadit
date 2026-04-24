<?php

use App\Actions\Stripe\CancelStripeCheckout;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;

beforeEach(function () {
    setUpTenantTest();

    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
});

test('sets order payment status to unpaid', function () {
    $order = Order::factory()
        ->for(test()->customer)
        ->recycle(test()->user)
        ->create();

    resolve(CancelStripeCheckout::class)($order);

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});
