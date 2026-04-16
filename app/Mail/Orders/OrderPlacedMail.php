<?php

namespace App\Mail\Orders;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderPlacedMail extends BaseMailable
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
            subject: "Order #{$this->order->order_number} Received — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.order-placed',
            with: [
                'order' => $this->order,
            ],
        );
    }
}
