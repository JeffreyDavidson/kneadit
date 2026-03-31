<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use App\Mail\PaymentFailedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentFailedEmailListener extends QueuedListener
{
    public function handle(PaymentFailed $event): void
    {
        Mail::to($event->user->email)->send(new PaymentFailedMail($event->user));
    }

    public function failed(PaymentFailed $event, \Throwable $exception): void
    {
        Log::warning('Payment failed email could not be sent', [
            'user' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
