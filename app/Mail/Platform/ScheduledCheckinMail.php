<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ScheduledCheckinMail extends BaseMailable
{
    public function __construct(
        public string $body,
        public string $emailSubject,
        public ?string $bakerName = null,
        public ?string $tenantId = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        $adminUrl = $this->tenantId
            ? 'https://' . $this->tenantId . '.getkneadit.app/admin'
            : 'https://getkneadit.app';

        return new Content(
            view: 'emails.platform.scheduled-checkin',
            text: 'emails.platform.scheduled-checkin-text',
            with: [
                'body' => $this->body,
                'emailSubject' => $this->emailSubject,
                'bakerName' => $this->bakerName,
                'adminUrl' => $adminUrl,
            ],
        );
    }
}
