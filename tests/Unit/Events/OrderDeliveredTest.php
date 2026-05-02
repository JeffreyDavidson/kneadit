<?php

use App\Events\Orders\OrderDelivered;
use App\Listeners\Orders\DispatchOrderDeliveredWebhookListener;
use Illuminate\Support\Facades\Event;

test('OrderDelivered event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderDelivered::class, DispatchOrderDeliveredWebhookListener::class);
});
