<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Order;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderBaking extends BaseMailable
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
            subject: "Your Order #{$this->order->order_number} is Being Prepared - KneadIt Bakery",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-baking',
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
