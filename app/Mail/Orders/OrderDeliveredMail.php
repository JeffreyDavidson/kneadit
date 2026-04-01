<?php

namespace App\Mail\Orders;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Order;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderDeliveredMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Order #{$this->order->order_number} Delivered - KneadIt Bakery",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-delivered',
            with: [
                'order' => $this->order,
                'customer' => $this->order->customer,
                'orderItems' => $this->order->orderItems()->with('product')->get(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
