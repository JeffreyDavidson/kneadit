<?php

namespace App\Listeners\Customers;

use App\Events\Customers\ReviewRequested;
use App\Listeners\SendEmailListener;
use App\Mail\Customers\ReviewRequestMail;
use Illuminate\Contracts\Mail\Mailable;

class SendReviewRequestEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var ReviewRequested $event */
        return $event->order->customer?->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var ReviewRequested $event */
        $event->order->loadMissing('orderItems.product');

        return new ReviewRequestMail($event->order);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var ReviewRequested $event */
        return ['order' => $event->order->order_number];
    }
}
