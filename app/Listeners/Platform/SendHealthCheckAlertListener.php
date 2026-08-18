<?php

namespace App\Listeners\Platform;

use App\Events\Platform\HealthCheckFailed;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\HealthAlertMail;
use Illuminate\Contracts\Mail\Mailable;

class SendHealthCheckAlertListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return config('mail.platform_notify');
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var HealthCheckFailed $event */
        return new HealthAlertMail($event->message);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        return ['check' => class_basename($event)];
    }
}
