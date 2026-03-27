<?php

use App\Events\OrderStatusChanged;
use App\Listeners\DispatchOrderUpdatedWebhook;
use App\Listeners\SendOrderStatusEmail;
use Illuminate\Support\Facades\Event;

test('OrderStatusChanged event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderStatusChanged::class, DispatchOrderUpdatedWebhook::class);
    Event::assertListening(OrderStatusChanged::class, SendOrderStatusEmail::class);
});
