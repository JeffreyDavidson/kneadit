<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Config;

class ScheduledCheckinMail extends BaseMailable
{
    public function __construct(
        public string $body,
        public string $emailSubject,
        public ?string $bakerName = null,
        public ?string $adminUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform.scheduled-checkin',
            text: 'emails.platform.scheduled-checkin-text',
            with: [
                'body' => $this->body,
                'emailSubject' => $this->emailSubject,
                'bakerName' => $this->bakerName,
                'adminUrl' => $this->adminUrl ?? Config::string('app.url'),
            ],
        );
    }
}
