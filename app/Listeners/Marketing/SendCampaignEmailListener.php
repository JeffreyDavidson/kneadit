<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\CampaignEmailQueued;
use App\Listeners\SendEmailListener;
use App\Mail\Marketing\CustomerBlastMail;
use Illuminate\Contracts\Mail\Mailable;

class SendCampaignEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var CampaignEmailQueued $event */
        return $event->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var CampaignEmailQueued $event */
        return new CustomerBlastMail($event->subject, $event->body);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var CampaignEmailQueued $event */
        return ['email' => $event->email];
    }
}
