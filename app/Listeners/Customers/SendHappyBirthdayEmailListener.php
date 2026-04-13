<?php

namespace App\Listeners\Customers;

use App\Events\Customers\CustomerBirthday;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\HappyBirthdayMail;
use Illuminate\Contracts\Mail\Mailable;

class SendHappyBirthdayEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var CustomerBirthday $event */
        return $event->customer->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var CustomerBirthday $event */
        return new HappyBirthdayMail($event->customer, $event->coupon);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var CustomerBirthday $event */
        return ['customer' => $event->customer->name];
    }
}
