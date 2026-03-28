<?php

use App\Events\OrderCreated;
use App\Listeners\DispatchOrderCreatedWebhookListener;
use App\Listeners\NotifyBakerOfNewOrderListener;
use App\Listeners\SendOrderPlacedEmailListener;
use Illuminate\Support\Facades\Event;

test('OrderCreated event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderCreated::class, DispatchOrderCreatedWebhookListener::class);
    Event::assertListening(OrderCreated::class, NotifyBakerOfNewOrderListener::class);
    Event::assertListening(OrderCreated::class, SendOrderPlacedEmailListener::class);
});
