<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\NewOrderNotification;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyBakerOfNewOrder implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function handle(OrderCreated $event): void
    {
        $bakerEmail = Setting::get('store_email');

        if (! $bakerEmail) {
            return;
        }

        Mail::to($bakerEmail)->send(new NewOrderNotification($event->order));
    }

    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::warning('Baker notification email failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
