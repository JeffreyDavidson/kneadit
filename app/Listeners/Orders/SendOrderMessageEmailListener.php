<?php

namespace App\Listeners\Orders;

use App\DataTransferObjects\Settings\EngagementSettings;
use App\Enums\Orders\SenderType;
use App\Events\Orders\OrderMessageSent;
use App\Listeners\SendEmailListener;
use App\Mail\Orders\NewOrderMessageMail;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\Mail\Mailable;

class SendOrderMessageEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var OrderMessageSent $event */
        $message = $event->message;
        $order = $message->order;

        if (! $order) {
            return null;
        }

        // Customer→bakery direction always emails the bakery so they can
        // respond. The toggle gates the bakery→customer notification side.
        if ($message->sender_type === SenderType::Customer) {
            return app(TenantSettings::class)->store->email ?: null;
        }

        if (! app(EngagementSettings::class)->emailOrderMessageEnabled) {
            return null;
        }

        return $order->customer?->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var OrderMessageSent $event */
        return new NewOrderMessageMail($event->message);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var OrderMessageSent $event */
        return ['order' => $event->message->order?->order_number];
    }
}
