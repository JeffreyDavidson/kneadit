<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use App\Mail\HealthAlertMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Number;

class SendPaymentFailedAlertListener extends QueuedListener
{
    public function handle(PaymentFailed $event): void
    {
        $message = "Payment failed for {$event->user->name} ({$event->user->email})"
            . ($event->tenant ? " — Tenant: {$event->tenant->store_name} ({$event->tenant->id})" : '')
            . "\nAmount: " . Number::currency($event->amount);

        Mail::to(config('mail.platform_notify'))->send(new HealthAlertMail($message));
    }

    public function failed(PaymentFailed $event, \Throwable $exception): void
    {
        Log::warning('Payment failed platform alert could not be sent', [
            'user' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
