<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderPlaced;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedEmail
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (! $order->customer?->email) {
            return;
        }

        try {
            Mail::to($order->customer->email)->send(new OrderPlaced($order));
        } catch (\Exception $e) {
            Log::warning('Order placed email failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
