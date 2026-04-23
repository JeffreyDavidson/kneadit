<?php

namespace App\Listeners\Customers;

use App\Events\Customers\ProductWaitlistJoined;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\NewWaitlistJoinNotificationMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;

class NotifyBakerOfWaitlistJoinListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return app(TenantSettings::class)->store->email ?: null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var ProductWaitlistJoined $event */
        return new NewWaitlistJoinNotificationMail($event->entry);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var ProductWaitlistJoined $event */
        return ['waitlist_entry' => $event->entry->id];
    }
}
