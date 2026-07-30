<?php

namespace App\Listeners\Orders;

use App\DataTransferObjects\Settings\EngagementSettings;
use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\SendEmailListener;
use App\Mail\Orders\OrderStatusMail;
use Illuminate\Contracts\Mail\Mailable;

class SendOrderStatusEmailListener extends SendEmailListener
{
    protected function getRecipient(object $event): ?string
    {
        /** @var OrderStatusChanged $event */
        if (! $this->isEmailableStatus($event->to)) {
            return null;
        }

        // Per-status email toggle. Tenant can disable individual status emails
        // (e.g., skip the "Baking" email but keep "Ready" + "Delivered").
        if (! resolve(EngagementSettings::class)->isOrderStatusEmailEnabled($event->to)) {
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

    private function isEmailableStatus(OrderStatus $status): bool
    {
        return match ($status) {
            OrderStatus::Confirmed,
            OrderStatus::Baking,
            OrderStatus::Ready,
            OrderStatus::Delivered,
            OrderStatus::Cancelled => true,
            default => false,
        };
    }
}
