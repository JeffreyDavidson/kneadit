<?php

namespace App\Listeners\Customers;

use App\Events\Customers\ContactMessageReceived;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\NewContactMessageNotificationMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;

class NotifyBakerOfContactMessageListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return resolve(TenantSettings::class)->store->email ?: null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var ContactMessageReceived $event */
        return new NewContactMessageNotificationMail($event->message);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var ContactMessageReceived $event */
        return ['contact_message' => $event->message->id];
    }
}
