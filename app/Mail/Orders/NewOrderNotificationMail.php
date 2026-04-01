<?php

namespace App\Mail\Orders;

use App\Mail\BaseMailable;
use App\Models\Order;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewOrderNotificationMail extends BaseMailable
{
    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Order #{$this->order->order_number} — \${$this->order->total}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order-notification',
            with: [
                'order' => $this->order,
            ],
        );
    }
}
