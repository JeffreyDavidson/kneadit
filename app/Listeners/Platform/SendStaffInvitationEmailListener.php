<?php

namespace App\Listeners\Platform;

use App\Events\Platform\StaffInvitationSent;
use App\Listeners\SendEmailListener;
use App\Mail\Platform\StaffInvitationMail;
use Illuminate\Contracts\Mail\Mailable;

class SendStaffInvitationEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var StaffInvitationSent $event */
        return $event->invitation->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var StaffInvitationSent $event */
        return new StaffInvitationMail($event->invitation, $event->storeName, $event->acceptUrl);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var StaffInvitationSent $event */
        return ['email' => $event->invitation->email];
    }
}
