<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReady extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');
        $replyTo = Setting::get('store_email');

        $envelope = new Envelope(
            subject: "Order #{$this->order->order_number} is Ready! — {$storeName}",
        );

        if ($replyTo) {
            $envelope->replyTo($replyTo);
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order-ready',
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
