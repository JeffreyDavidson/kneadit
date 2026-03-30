<?php

namespace App\Listeners;

use App\Enums\SenderType;
use App\Events\OrderMessageSent;
use App\Mail\NewOrderMessageMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderMessageEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(OrderMessageSent $event): void
    {
        $message = $event->message;
        $order = $message->order;

        if (! $order) {
            return;
        }

        if ($message->sender_type === SenderType::Customer) {
            // Customer sent a message — notify the baker
            $storeEmail = settings('store_email');
            if ($storeEmail) {
                Mail::to($storeEmail)->send(new NewOrderMessageMail($message));
            }
        } else {
            // Baker sent a message — notify the customer
            $customerEmail = $order->customer?->email;
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new NewOrderMessageMail($message));
            }
        }
    }

    public function failed(OrderMessageSent $event, \Throwable $exception): void
    {
        Log::warning('Order message email failed', [
            'order' => $event->message->order?->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
