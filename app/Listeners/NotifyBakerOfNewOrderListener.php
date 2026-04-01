<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\NewOrderNotificationMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyBakerOfNewOrderListener extends QueuedListener
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $order->loadMissing('orderItems.product');

        $bakerEmail = app(TenantSettings::class)->storeEmail;

        if (! $bakerEmail) {
            return;
        }

        Mail::to($bakerEmail)->send(new NewOrderNotificationMail($order));
    }

    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::warning('Baker notification email failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
