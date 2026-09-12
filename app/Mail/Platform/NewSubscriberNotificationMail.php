<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewSubscriberNotificationMail extends BaseMailable
{
    public function __construct(
        public string $bakerName,
        public string $bakerEmail,
        public string $storeName,
        public string $storefrontHost,
        public string $plan,
        public string $centralAdminUrl,
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
            view: 'emails.platform.new-subscriber-notification',
        );
    }
}
