<?php

namespace App\Listeners\Platform;

use App\Events\Platform\ScheduledCheckinDue;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\ScheduledCheckinMail;
use Illuminate\Contracts\Mail\Mailable;

class SendScheduledCheckinEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var ScheduledCheckinDue $event */
        return $event->tenantEmail;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var ScheduledCheckinDue $event */
        return new ScheduledCheckinMail($event->body, $event->subject);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var ScheduledCheckinDue $event */
        return ['email' => $event->tenantEmail];
    }
}
