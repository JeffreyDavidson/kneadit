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

    /**
     * A platform email about the platform being unhealthy: it must render even
     * when the database is down, so it skips the bakery branding data that
     * BaseMailable reads from the tenant settings.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function buildViewData(): array
    {
        return [
            'alertMessage' => $this->alertMessage,
            'alertSubject' => $this->alertSubject,
        ];
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.platform.health-alert-text',
        );
    }
}
