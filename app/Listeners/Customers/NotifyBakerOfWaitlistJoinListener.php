<?php

namespace App\Listeners\Customers;

use App\Events\Customers\ProductWaitlistJoined;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\NewWaitlistJoinNotificationMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;

class NotifyBakerOfWaitlistJoinListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        $recipient = resolve(TenantSettings::class)->store->email ?: null;

        if (! $recipient) {
            /** @var ProductWaitlistJoined $event */
            Log::warning('Waitlist join notification skipped because no store email is configured', [
                'waitlist_entry' => $event->entry->id,
            ]);
        }

        return $recipient;
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
