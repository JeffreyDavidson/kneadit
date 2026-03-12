<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');

        return new Envelope(
            subject: "Order #{$this->order->order_number} Received — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed',
            with: [
                'order' => $this->order,
                'storeName' => Setting::get('store_name', 'KneadIt Bakery'),
            ],
        );
    }
}
