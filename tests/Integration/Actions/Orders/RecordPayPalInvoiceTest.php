<?php

use App\Actions\Orders\RecordPayPalInvoice;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;

beforeEach(function () {
    setUpTenantTest();

    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
});

test('stores paypal invoice id on order', function () {
    $order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->create();

    resolve(RecordPayPalInvoice::class)($order, 'INV2-XXXX-YYYY-ZZZZ');

    expect($order->refresh()->paypal_invoice_id)->toBe('INV2-XXXX-YYYY-ZZZZ');
});
