<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderModified;
use App\Listeners\SendEmailListener;
use App\Mail\Orders\OrderModifiedMail;
use Illuminate\Contracts\Mail\Mailable;

class SendOrderModifiedEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var OrderModified $event */
        return $event->order->customer?->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var OrderModified $event */
        $event->order->loadMissing('orderItems.product');

        return new OrderModifiedMail(
            $event->order,
            $event->previousSubtotal,
            $event->previousTotal,
        );
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var OrderModified $event */
        return ['order' => $event->order->order_number];
    }
}
