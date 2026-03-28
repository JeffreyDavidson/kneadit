<?php

namespace App\Listeners;

use App\Events\OrderMessageSent;
use App\Mail\NewOrderMessageMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderMessageEmailListener implements ShouldQueue
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(OrderMessageSent $event): void
    {
        $message = $event->message;
        $customerEmail = $message->order?->customer?->email;

        if (! $customerEmail) {
            return;
        }

        Mail::to($customerEmail)->send(new NewOrderMessageMail($message));
    }

    public function failed(OrderMessageSent $event, \Throwable $exception): void
    {
        Log::warning('Order message email failed', [
            'order' => $event->message->order?->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
