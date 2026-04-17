<?php

namespace App\Listeners\Orders;

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\SendEmailListener;
use App\Mail\Orders\OrderStatusMail;
use Illuminate\Contracts\Mail\Mailable;

class SendOrderStatusEmailListener extends SendEmailListener
{
    /** @var array<int, OrderStatus> */
    private const EMAILABLE_STATUSES = [
        OrderStatus::Confirmed,
        OrderStatus::Baking,
        OrderStatus::Ready,
        OrderStatus::Delivered,
        OrderStatus::Cancelled,
    ];

    protected function getRecipient(object $event): ?string
    {
        /** @var OrderStatusChanged $event */
        if (! in_array($event->to, self::EMAILABLE_STATUSES, true)) {
            return null;
        }

        return $event->order->customer?->email;
    }

    protected function getMailable(object $event): Mailable
    {
        /** @var OrderStatusChanged $event */
        $event->order->loadMissing('orderItems.product');

        return new OrderStatusMail($event->order, $event->to);
    }

    /** @return array<string, mixed> */
    protected function getFailureContext(object $event): array
    {
        /** @var OrderStatusChanged $event */
        return [
            'order' => $event->order->order_number,
            'transition' => "{$event->from->value} -> {$event->to->value}",
        ];
    }
}
