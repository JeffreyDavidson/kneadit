<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Listeners\SendEmailListener;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;

class NotifyBakerOfNewOrderListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        return resolve(TenantSettings::class)->store->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var OrderCreated $event */
        $event->order->loadMissing('orderItems.product');

        return new NewOrderNotificationMail($event->order);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var OrderCreated $event */
        return ['order' => $event->order->order_number];
    }
}
