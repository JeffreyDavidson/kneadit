<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use App\Mail\PaymentFailedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentFailedEmailListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
