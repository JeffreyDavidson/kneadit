<?php

use App\Events\OrderStatusChanged;
use App\Listeners\DispatchOrderUpdatedWebhookListener;
use App\Listeners\SendOrderStatusEmailListener;
use Illuminate\Support\Facades\Event;

test('OrderStatusChanged event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderStatusChanged::class, DispatchOrderUpdatedWebhookListener::class);
    Event::assertListening(OrderStatusChanged::class, SendOrderStatusEmailListener::class);
});
