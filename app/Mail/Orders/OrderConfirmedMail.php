<?php

namespace App\Mail\Orders;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderConfirmedMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = app(TenantSettings::class)->storeName;

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Order #{$this->order->order_number} Confirmed — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.orders.order-confirmed',
            with: [
                'order' => $this->order,
                'customer' => $this->order->customer,
                'orderItems' => $this->order->orderItems()->with('product')->get(),
            ],
        );
    }
}
