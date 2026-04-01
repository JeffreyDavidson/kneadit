<?php

use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\DispatchOrderCreatedWebhookListener;
use App\Listeners\Orders\NotifyBakerOfNewOrderListener;
use App\Listeners\Orders\SendOrderPlacedEmailListener;
use Illuminate\Support\Facades\Event;

test('OrderCreated event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderCreated::class, DispatchOrderCreatedWebhookListener::class);
    Event::assertListening(OrderCreated::class, NotifyBakerOfNewOrderListener::class);
    Event::assertListening(OrderCreated::class, SendOrderPlacedEmailListener::class);
});
