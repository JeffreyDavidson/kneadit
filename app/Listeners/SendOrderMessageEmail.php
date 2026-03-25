<?php

namespace App\Listeners;

use App\Events\OrderMessageSent;
use App\Mail\NewOrderMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderMessageEmail
{
    public function handle(OrderMessageSent $event): void
    {
        $message = $event->message;
        $customerEmail = $message->order?->customer?->email;

        if (! $customerEmail) {
            return;
        }

        try {
            Mail::to($customerEmail)->send(new NewOrderMessage($message));
        } catch (\Exception $e) {
            Log::warning('Order message email failed', [
                'order' => $message->order?->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
