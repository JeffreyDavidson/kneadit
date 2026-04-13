<?php

namespace App\Listeners\Marketing;

use App\Events\Marketing\CateringQuoteRequested;
use App\Listeners\SendEmailListener;
use App\Mail\Marketing\CateringQuoteMail;
use Illuminate\Contracts\Mail\Mailable;

class SendCateringQuoteEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var CateringQuoteRequested $event */
        return $event->inquiry->customer_email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var CateringQuoteRequested $event */
        return new CateringQuoteMail($event->inquiry);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var CateringQuoteRequested $event */
        return ['inquiry' => $event->inquiry->id];
    }
}
