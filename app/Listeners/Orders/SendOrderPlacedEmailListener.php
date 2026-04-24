<?php

namespace App\Listeners\Orders;

use App\DataTransferObjects\Settings\EngagementSettings;
use App\Events\Orders\OrderCreated;
use App\Listeners\SendEmailListener;
use App\Mail\Orders\OrderPlacedMail;
use Illuminate\Contracts\Mail\Mailable;

class SendOrderPlacedEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var OrderCreated $event */
        if (! resolve(EngagementSettings::class)->emailOrderPlacedEnabled) {
            return null;
        }

        return $event->order->customer?->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var OrderCreated $event */
        $event->order->loadMissing('orderItems.product');

        return new OrderPlacedMail($event->order);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var OrderCreated $event */
        return ['order' => $event->order->order_number];
    }
}
