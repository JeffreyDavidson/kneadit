<?php

use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\DispatchOrderWebhookListener;
use App\Listeners\Orders\SendOrderStatusEmailListener;
use Illuminate\Support\Facades\Event;

test('OrderStatusChanged event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderStatusChanged::class, DispatchOrderWebhookListener::class);
    Event::assertListening(OrderStatusChanged::class, SendOrderStatusEmailListener::class);
});
