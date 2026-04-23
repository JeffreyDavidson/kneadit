<?php

namespace App\Mail\Orders;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Orders\Order;
use App\ValueObjects\Money;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderModifiedMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Order $order,
        public Money $previousSubtotal,
        public Money $previousTotal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Your order #{$this->order->order_number} was updated",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.order-modified',
            with: [
                'order' => $this->order,
                'previousSubtotal' => $this->previousSubtotal,
                'previousTotal' => $this->previousTotal,
            ],
        );
    }
}
