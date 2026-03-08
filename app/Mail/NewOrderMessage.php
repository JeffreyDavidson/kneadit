<?php

namespace App\Mail;

use App\Models\OrderMessage;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrderMessage $orderMessage
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');
        $orderNumber = $this->orderMessage->order->order_number;

        return new Envelope(
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
                'storeName' => Setting::get('store_name', 'KneadIt Bakery'),
            ],
        );
    }
}
