<?php

use App\Events\Orders\OrderMessageSent;
use App\Listeners\Orders\SendOrderMessageEmailListener;
use App\Mail\Orders\NewOrderMessageMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends email to the baker when a customer sends a message', function () {
    Mail::fake();
    settings(['store_email' => 'baker@example.com']);

    $customer = Customer::factory()->create(['email' => 'customer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $message = OrderMessage::factory()->fromCustomer()->for($order)->create();

    $event = new OrderMessageSent($message);

    $listener = new SendOrderMessageEmailListener;
    $listener->handle($event);

    Mail::assertQueued(NewOrderMessageMail::class, fn (NewOrderMessageMail $mail) => $mail->hasTo('baker@example.com'));
});

test('it sends email to the customer when the baker sends a message', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'customer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $message = OrderMessage::factory()->fromBaker()->for($order)->create();

    $event = new OrderMessageSent($message);

    $listener = new SendOrderMessageEmailListener;
    $listener->handle($event);

    Mail::assertQueued(NewOrderMessageMail::class, fn (NewOrderMessageMail $mail) => $mail->hasTo('customer@example.com'));
});

test('it does not send email when the order is missing', function () {
    Mail::fake();

    $message = OrderMessage::factory()->fromCustomer()->create();
    $message->setRelation('order', null);

    $event = new OrderMessageSent($message);

    $listener = new SendOrderMessageEmailListener;
    $listener->handle($event);

    Mail::assertNothingQueued();
});
