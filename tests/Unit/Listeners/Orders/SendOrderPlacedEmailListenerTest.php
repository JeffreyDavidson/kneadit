<?php

use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\SendOrderPlacedEmailListener;
use App\Mail\Orders\OrderPlacedMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends order placed email to customer', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'customer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $event = new OrderCreated($order);

    $listener = new SendOrderPlacedEmailListener;
    $listener->handle($event);

    Mail::assertQueued(
        OrderPlacedMail::class,
        fn (OrderPlacedMail $mail) => $mail->hasTo('customer@example.com'),
    );
});

test('it loads order items before sending', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'test@example.com']);
    $order = Order::factory()->for($customer)->create();
    $event = new OrderCreated($order);

    $listener = new SendOrderPlacedEmailListener;
    $listener->handle($event);

    Mail::assertQueued(OrderPlacedMail::class);
});
