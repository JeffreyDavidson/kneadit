<?php

namespace App\Listeners\Customers;

use App\Events\Customers\RepeatOrderReminderDue;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\RepeatOrderReminderMail;
use Illuminate\Contracts\Mail\Mailable;

class SendRepeatOrderReminderEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var RepeatOrderReminderDue $event */
        return $event->customer->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var RepeatOrderReminderDue $event */
        return new RepeatOrderReminderMail($event->customer, $event->daysSinceLastOrder);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var RepeatOrderReminderDue $event */
        return ['customer' => $event->customer->name];
    }
}
