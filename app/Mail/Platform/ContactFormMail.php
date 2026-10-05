<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactFormMail extends PlatformMail
{
    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->senderEmail],
            subject: "KneadIt Contact: {$this->senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform.contact-form',
        );
    }
}
