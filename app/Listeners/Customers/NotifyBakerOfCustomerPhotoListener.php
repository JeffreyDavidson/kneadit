<?php

namespace App\Listeners\Customers;

use App\Events\Customers\CustomerPhotoSubmitted;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\NewCustomerPhotoNotificationMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;

class NotifyBakerOfCustomerPhotoListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return resolve(TenantSettings::class)->store->email ?: null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var CustomerPhotoSubmitted $event */
        return new NewCustomerPhotoNotificationMail($event->photo);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var CustomerPhotoSubmitted $event */
        return ['customer_photo' => $event->photo->id];
    }
}
