<?php

use App\Events\OrderMessageSent;
use App\Listeners\SendOrderMessageEmailListener;
use Illuminate\Support\Facades\Event;

test('OrderMessageSent event has correct listeners', function () {
    Event::fake();
    Event::assertListening(OrderMessageSent::class, SendOrderMessageEmailListener::class);
});
