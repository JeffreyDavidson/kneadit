<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RepeatOrderReminder extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    use BakerBranded;
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public int $daysSinceLastOrder
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

    public function attachments(): array
    {
        return [];
    }
}
