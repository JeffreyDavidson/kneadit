<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HealthAlertMail extends BaseMailable
{
    public function __construct(
        public string $alertMessage,
        public string $alertSubject = '⚠️ KneadIt Health Check Alert',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->alertSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.platform.health-alert-text',
        );
    }
}
