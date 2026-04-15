<?php

namespace App\Listeners\Platform;

use App\Events\Platform\TrialExpired;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\TrialExpiredMail;
use Illuminate\Contracts\Mail\Mailable;

class SendTrialExpiredEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var TrialExpired $event */
        return $event->user->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var TrialExpired $event */
        return new TrialExpiredMail($event->user, $event->tenantId);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var TrialExpired $event */
        return ['email' => $event->user->email];
    }
}
