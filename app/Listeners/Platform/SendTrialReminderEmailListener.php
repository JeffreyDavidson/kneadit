<?php

namespace App\Listeners\Platform;

use App\Events\Platform\TrialReminding;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\TrialReminderMail;
use Illuminate\Contracts\Mail\Mailable;

class SendTrialReminderEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var TrialReminding $event */
        return $event->user->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var TrialReminding $event */
        return new TrialReminderMail($event->user, $event->storeName, $event->daysLeft);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var TrialReminding $event */
        return ['email' => $event->user->email];
    }
}
