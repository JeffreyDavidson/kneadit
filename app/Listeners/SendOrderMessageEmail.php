<?php

namespace App\Listeners;

use App\Events\OrderMessageSent;
use App\Mail\NewOrderMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderMessageEmail implements ShouldQueue
{
    public function handle(OrderMessageSent $event): void
    {
        $message = $event->message;
        $customerEmail = $message->order?->customer?->email;

        if (! $customerEmail) {
            return;
        }

        Mail::to($customerEmail)->send(new NewOrderMessage($message));
    }

    public function failed(OrderMessageSent $event, \Throwable $exception): void
    {
        Log::warning('Order message email failed', [
            'order' => $event->message->order?->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
