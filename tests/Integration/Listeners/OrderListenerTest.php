<?php

use App\Events\OrderCreated;
use App\Listeners\NotifyBakerOfNewOrderListener;
use App\Listeners\SendOrderPlacedEmailListener;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderPlacedMail;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('SendOrderPlacedEmailListener sends email to customer', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    $order = Order::factory()->recycle($customer)->create();
    $event = new OrderCreated($order);

    (new SendOrderPlacedEmailListener)->handle($event);

    Mail::assertQueued(OrderPlacedMail::class, fn ($mail) => $mail->hasTo('buyer@example.com'));
});

test('NotifyBakerOfNewOrderListener sends email to store email', function () {
    Mail::fake();
    settings(['store_email' => 'baker@example.com']);

    $order = Order::factory()->create();
    $event = new OrderCreated($order);

    (new NotifyBakerOfNewOrderListener)->handle($event);

    Mail::assertQueued(NewOrderNotificationMail::class, fn ($mail) => $mail->hasTo('baker@example.com'));
});

test('SendOrderStatusEmailListener sends confirmed email', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    $order = Order::factory()->recycle($customer)->create();
    $event = new App\Events\OrderStatusChanged($order, App\Enums\OrderStatus::Pending, App\Enums\OrderStatus::Confirmed);

    (new App\Listeners\SendOrderStatusEmailListener)->handle($event);

    Mail::assertQueued(App\Mail\OrderConfirmedMail::class, fn ($mail) => $mail->hasTo('buyer@example.com'));
});

test('SendOrderStatusEmailListener sends delivered email', function () {
    Mail::fake();

    $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
    $order = Order::factory()->recycle($customer)->create();
    $event = new App\Events\OrderStatusChanged($order, App\Enums\OrderStatus::Ready, App\Enums\OrderStatus::Delivered);

    (new App\Listeners\SendOrderStatusEmailListener)->handle($event);

    Mail::assertQueued(App\Mail\OrderDeliveredMail::class);
});
