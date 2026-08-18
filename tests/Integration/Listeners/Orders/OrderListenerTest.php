<?php

use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\NotifyBakerOfNewOrderListener;
use App\Listeners\Orders\SendOrderPlacedEmailListener;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
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
