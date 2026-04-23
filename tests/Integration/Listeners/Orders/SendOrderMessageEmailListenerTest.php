<?php

use App\Events\Orders\OrderMessageSent;
use App\Listeners\Orders\SendOrderMessageEmailListener;
use App\Mail\Orders\NewOrderMessageMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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

test('it does not send to the customer when the order_message email toggle is disabled', function () {
    Mail::fake();
    settings(['email_order_message_enabled' => false]);

    $customer = Customer::factory()->create(['email' => 'customer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $message = OrderMessage::factory()->fromBaker()->for($order)->create();

    (new SendOrderMessageEmailListener)->handle(new OrderMessageSent($message));

    Mail::assertNothingQueued();
});

test('toggle does not affect the customer-to-baker direction (baker still gets notified)', function () {
    Mail::fake();
    settings([
        'email_order_message_enabled' => false,
        'store_email' => 'baker@example.com',
    ]);

    $customer = Customer::factory()->create(['email' => 'customer@example.com']);
    $order = Order::factory()->for($customer)->create();
    $message = OrderMessage::factory()->fromCustomer()->for($order)->create();

    (new SendOrderMessageEmailListener)->handle(new OrderMessageSent($message));

    Mail::assertQueued(NewOrderMessageMail::class, fn (NewOrderMessageMail $mail) => $mail->hasTo('baker@example.com'));
});

test('it does not send email when customer sends message but no store email is configured', function () {
    Mail::fake();
    settings(['store_email' => '']);

    $customer = Customer::factory()->create();
    $order = Order::factory()->for($customer)->create();
    $message = OrderMessage::factory()->fromCustomer()->for($order)->create();

    $event = new OrderMessageSent($message);

    $listener = new SendOrderMessageEmailListener;
    $listener->handle($event);

    Mail::assertNothingQueued();
});

test('failed method logs a warning with order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendOrderMessageEmailListener failed', Mockery::on(fn (array $context) => $context['order'] === 'ORD-001'
            && $context['error'] === 'SMTP timeout'));

    $order = Order::factory()->create(['order_number' => 'ORD-001']);
    $message = OrderMessage::factory()->fromCustomer()->for($order)->create();
    $event = new OrderMessageSent($message);

    $listener = new SendOrderMessageEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
