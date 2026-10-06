<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class HealthAlertMail extends PlatformMail
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
            view: 'emails.platform.health-alert',
            text: 'emails.platform.health-alert-text',
        );
    }
}
