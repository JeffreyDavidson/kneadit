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
        return new ScheduledCheckinMail(
            body: $event->body,
            emailSubject: $event->subject,
            bakerName: $event->bakerName,
            tenantId: $event->tenantId,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var ScheduledCheckinDue $event */
        return ['email' => $event->tenantEmail];
    }
}
