<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Listeners\QueuedListener;
use App\Mail\Orders\OrderPlacedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedEmailListener extends QueuedListener
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $order->loadMissing('orderItems.product');

        if (! $order->customer?->email) {
            return;
        }

        Mail::to($order->customer->email)->send(new OrderPlacedMail($order));
    }

    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::warning('Order placed email failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
