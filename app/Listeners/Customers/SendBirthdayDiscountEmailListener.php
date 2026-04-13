<?php

namespace App\Listeners\Customers;

use App\Events\Customers\BirthdayDiscountGenerated;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\BirthdayDiscountMail;
use Illuminate\Contracts\Mail\Mailable;

class SendBirthdayDiscountEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var BirthdayDiscountGenerated $event */
        return $event->customer->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var BirthdayDiscountGenerated $event */
        return new BirthdayDiscountMail($event->customer, $event->coupon);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var BirthdayDiscountGenerated $event */
        return ['customer' => $event->customer->name];
    }
}
