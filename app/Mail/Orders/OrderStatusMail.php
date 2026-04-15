<?php

namespace App\Mail\Orders;

use App\Enums\Orders\OrderStatus;
use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderStatusMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Order $order,
        public OrderStatus $status,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $this->resolveSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            html: "emails.orders.order-{$this->status->value}",
            with: [
                'order' => $this->order,
                'customer' => $this->order->customer,
                'orderItems' => $this->order->orderItems,
            ],
        );
    }

    private function resolveSubject(): string
    {
        $number = $this->order->order_number;
        $storeName = app(TenantSettings::class)->storeName;

        return match ($this->status) {
            OrderStatus::Confirmed => "Order #{$number} Confirmed — {$storeName}",
            OrderStatus::Baking => "Your Order #{$number} is Being Prepared — {$storeName}",
            OrderStatus::Ready => "Order #{$number} is Ready! — {$storeName}",
            OrderStatus::Delivered => "Order #{$number} Delivered — {$storeName}",
            OrderStatus::Cancelled => "Order #{$number} Cancelled — {$storeName}",
            default => "Order #{$number} Update — {$storeName}",
        };
    }
}
