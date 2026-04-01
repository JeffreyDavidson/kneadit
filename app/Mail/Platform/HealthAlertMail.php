<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HealthAlertMail extends BaseMailable
{
    public function __construct(
        public string $alertMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ KneadIt Health Check Alert',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.platform.health-alert-text',
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
