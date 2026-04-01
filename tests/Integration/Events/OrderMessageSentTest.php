<?php

use App\Events\Orders\OrderMessageSent;
use App\Listeners\Orders\SendOrderMessageEmailListener;
use Illuminate\Support\Facades\Event;

test('OrderMessageSent event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderMessageSent::class, SendOrderMessageEmailListener::class);
});
