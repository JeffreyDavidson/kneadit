<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed extends Mailable implements ShouldQueue
{
    use BakerBranded;
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Order #{$this->order->order_number} Confirmed — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-confirmed',
            with: [
                'order' => $this->order,
                'customer' => $this->order->customer,
                'orderItems' => $this->order->orderItems()->with('product')->get(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
