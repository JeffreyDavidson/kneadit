<?php

namespace App\Listeners;

use App\Events\HealthCheckFailed;
use App\Mail\HealthAlertMail;
use Illuminate\Support\Facades\Mail;

class SendHealthCheckAlertListener extends QueuedListener
{
    public function handle(HealthCheckFailed $event): void
    {
        Mail::to(config('mail.platform_notify'))->send(
            new HealthAlertMail($event->message),
        );
    }
}
