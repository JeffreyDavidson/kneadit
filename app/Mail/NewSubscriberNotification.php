<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewSubscriberNotification extends BaseMailable
{
    public function __construct(
        public string $bakerName,
        public string $bakerEmail,
        public string $storeName,
        public string $subdomain,
        public string $plan,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New KneadIt Signup — {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-subscriber-notification',
        );
    }
}
