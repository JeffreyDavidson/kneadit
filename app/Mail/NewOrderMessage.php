<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\OrderMessage;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewOrderMessage extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public OrderMessage $orderMessage
    ) {}

    public function envelope(): Envelope
    {
        $storeName = settings('store_name', 'KneadIt Bakery');
        $orderNumber = $this->orderMessage->order?->order_number;

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "New Message on Order #{$orderNumber} — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order-message',
            with: [
                'orderMessage' => $this->orderMessage,
                'order' => $this->orderMessage->order,
                'storeName' => settings('store_name', 'KneadIt Bakery'),
            ],
        );
    }
}
