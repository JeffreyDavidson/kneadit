<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
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
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    use BakerBranded;
    use Queueable, SerializesModels;

    public function __construct(
        public OrderMessage $orderMessage
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');
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
                'storeName' => Setting::get('store_name', 'KneadIt Bakery'),
            ],
        );
    }
}
