<?php

use App\Events\OrderCreated;
use App\Listeners\DispatchOrderCreatedWebhook;
use App\Listeners\NotifyBakerOfNewOrder;
use App\Listeners\SendOrderPlacedEmail;
use Illuminate\Support\Facades\Event;

test('OrderCreated event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderCreated::class, DispatchOrderCreatedWebhook::class);
    Event::assertListening(OrderCreated::class, NotifyBakerOfNewOrder::class);
    Event::assertListening(OrderCreated::class, SendOrderPlacedEmail::class);
});
