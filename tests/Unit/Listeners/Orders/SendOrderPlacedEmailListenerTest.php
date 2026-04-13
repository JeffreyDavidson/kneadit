<?php

use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\SendOrderPlacedEmailListener;
use App\Mail\Orders\OrderPlacedMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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

test('failed method logs a warning with order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('SendOrderPlacedEmailListener failed', Mockery::on(fn (array $context) => $context['order'] === 'ORD-001'
            && $context['error'] === 'SMTP timeout'));

    $order = Order::factory()->create(['order_number' => 'ORD-001']);
    $event = new OrderCreated($order);

    $listener = new SendOrderPlacedEmailListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
