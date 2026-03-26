<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Customer;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RepeatOrderReminder extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Customer $customer,
        public int $daysSinceLastOrder,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: 'We Miss You! 🥖 Your Favorite Treats Are Waiting',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.repeat-order-reminder',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
