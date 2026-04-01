<?php

namespace App\Listeners\Platform;

use App\Events\Platform\HealthCheckFailed;
use App\Listeners\QueuedListener;
use App\Mail\Platform\HealthAlertMail;
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
