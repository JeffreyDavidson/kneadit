<?php

namespace App\Listeners\Customers;

use App\Events\Customers\LowReviewReceived;
use App\Listeners\SendEmailListener;
use App\Mail\Operations\LowReviewAlertMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;

class SendLowReviewAlertListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return resolve(TenantSettings::class)->store->email ?: null;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var LowReviewReceived $event */
        return new LowReviewAlertMail($event->review);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var LowReviewReceived $event */
        return ['review' => $event->review->id];
    }
}
