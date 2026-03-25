<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\NewOrderNotification;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyBakerOfNewOrder
{
    public function handle(OrderCreated $event): void
    {
        $bakerEmail = Setting::get('store_email');

        if (! $bakerEmail) {
            return;
        }

        try {
            Mail::to($bakerEmail)->send(new NewOrderNotification($event->order));
        } catch (\Exception $e) {
            Log::warning('Baker notification email failed', [
                'order' => $event->order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
