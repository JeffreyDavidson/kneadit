<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderPlacedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedEmailListener extends QueuedListener
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

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
