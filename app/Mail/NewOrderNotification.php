<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

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
                'storeName' => Setting::get('store_name', 'KneadIt Bakery'),
            ],
        );
    }
}
