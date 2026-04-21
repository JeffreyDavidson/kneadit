<?php

use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\NotifyBakerOfNewOrderListener;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it sends notification email to the baker', function () {
    Mail::fake();
    settings(['store_email' => 'baker@example.com']);

    $order = Order::factory()->create();
    $event = new OrderCreated($order);

    $listener = new NotifyBakerOfNewOrderListener;
    $listener->handle($event);

    Mail::assertQueued(
        NewOrderNotificationMail::class,
        fn (NewOrderNotificationMail $mail) => $mail->hasTo('baker@example.com'),
    );
});

test('it does not send email when baker email is not configured', function () {
    Mail::fake();
    settings(['store_email' => '']);

    $order = Order::factory()->create();
    $event = new OrderCreated($order);

    $listener = new NotifyBakerOfNewOrderListener;
    $listener->handle($event);

    Mail::assertNothingQueued();
});

test('failed method logs a warning with order number and error message', function () {
    Log::shouldReceive('warning')
        ->once()
        ->with('NotifyBakerOfNewOrderListener failed', Mockery::on(fn (array $context) => $context['order'] === 'ORD-001'
            && $context['error'] === 'SMTP timeout'));

    $order = Order::factory()->create(['order_number' => 'ORD-001']);
    $event = new OrderCreated($order);

    $listener = new NotifyBakerOfNewOrderListener;
    $listener->failed($event, new RuntimeException('SMTP timeout'));
});
