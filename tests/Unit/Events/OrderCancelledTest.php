<?php

use App\Events\Orders\OrderCancelled;
use App\Listeners\Orders\DispatchOrderCancelledWebhookListener;
use Illuminate\Support\Facades\Event;

test('OrderCancelled event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderCancelled::class, DispatchOrderCancelledWebhookListener::class);
});
