<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\CateringInquiryReceived;
use App\Listeners\SendEmailListener;
use App\Mail\Marketing\NewCateringInquiryNotificationMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;

class NotifyBakerOfCateringInquiryListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return app(TenantSettings::class)->store->email ?: null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var CateringInquiryReceived $event */
        return new NewCateringInquiryNotificationMail($event->inquiry);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var CateringInquiryReceived $event */
        return ['inquiry' => $event->inquiry->id];
    }
}
