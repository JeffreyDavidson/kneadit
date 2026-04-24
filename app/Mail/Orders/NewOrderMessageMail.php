<?php

namespace App\Mail\Orders;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Orders\OrderMessage;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewOrderMessageMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public OrderMessage $orderMessage,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = resolve(TenantSettings::class)->store->name;
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
            view: 'emails.orders.new-order-message',
            with: [
                'orderMessage' => $this->orderMessage,
                'order' => $this->orderMessage->order,
            ],
        );
    }
}
